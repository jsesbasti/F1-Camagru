<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Core\Session;
use App\Models\Image;
use App\Models\Overlay;
use App\Models\User;

class PhotoController extends Controller
{
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads/photos';
    private const UPLOAD_URL = '/uploads/photos';
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const MAX_SIDE = 2000;
    private const MEDIA_MAX_BYTES = 30 * 1024 * 1024;
    private const MEDIA_WIDTH = 720;
    // Height / width of the formats a post can have: square and vertical 4:5.
    private const ASPECTS = ['1:1' => 1.0, '4:5' => 1.25];
    private const MEDIA_MAX_SECONDS = 15;
    private const MEDIA_TYPES = ['image/gif', 'video/mp4', 'video/webm', 'video/quicktime'];

    public function index(): void
    {
        $this->requireAuth();
        $id = Session::getId();
        $this->view('photo', [
            'title' => 'Upload Photo',
            'currentUser' => User::find(id: $id),
            'overlays' => Overlay::all(),
            'photos' => Image::forStudio($id),
            'scripts' => [
                'photo'
            ]
        ]);
    }

    public function saveDraft(): void
    {
        $userId = $this->requireSession();
        $overlays = $this->overlays(required: false);

        $draftId = (int) ($_POST['draft_id'] ?? 0);
        if ($draftId) {
            $draft = $this->ownedDraft($draftId, $userId);
            $source = $draft['source_filename'] ?? $draft['filename'];

            $filename = $this->saveComposed($this->loadPng(self::path($source)), $overlays, 'draft_');
            if (!Image::updateDraft($draftId, $filename, $source, $overlays)) {
                @unlink(self::path($filename));
                $this->fail(['error' => 'Could not save draft'], 500);
            }
            if ($draft['filename'] !== $source) {
                @unlink(self::path($draft['filename']));
            }
            $id = $draftId;
        } else {
            $base = $this->uploadedPng();
            $source = $this->savePng($base, 'src_');
            $filename = $this->saveComposed($base, $overlays, 'draft_');

            $id = Image::create([
                'user_id' => $userId,
                'filename' => $filename,
                'source_filename' => $source,
                'description' => '',
                'is_draft' => 1,
                'overlays' => json_encode($overlays)
            ]);
        }

        Helper::json_response([
            'success' => true,
            'photo' => Image::studioEntry([
                'id' => $id,
                'filename' => $filename,
                'source_filename' => $source,
                'is_draft' => 1,
                'overlays' => json_encode($overlays)
            ])
        ]);
    }

    public function publish(): void
    {
        $userId = $this->requireSession();
        $overlays = $this->overlays(required: true);

        $description = trim((string) ($_POST['description'] ?? ''));
        if (mb_strlen($description) > 2200) {
            $this->fail(['description' => 'Caption is too long']);
        }

        $draftId = (int) ($_POST['draft_id'] ?? 0);
        $draft = $draftId ? $this->ownedDraft($draftId, $userId) : null;

        if (!$draft && isset($_FILES['media'])) {
            $filename = $this->publishMedia($overlays);
        } else {
            $base = $draft
                ? $this->loadPng(self::path($draft['source_filename'] ?? $draft['filename']))
                : $this->uploadedPng();
            $filename = $this->saveComposed($base, $overlays, 'img_');
        }

        if ($draft) {
            if (!Image::publishDraft($draftId, $filename, $description)) {
                @unlink(self::path($filename));
                $this->fail(['error' => 'Could not publish photo'], 500);
            }
            @unlink(self::path($draft['filename']));
            if ($draft['source_filename']) {
                @unlink(self::path($draft['source_filename']));
            }
            $id = $draftId;
        } else {
            $id = Image::create([
                'user_id' => $userId,
                'filename' => $filename,
                'description' => $description
            ]);
        }

        Helper::json_response([
            'success' => true,
            'photo' => Image::studioEntry(['id' => $id, 'filename' => $filename, 'source_filename' => null, 'is_draft' => 0, 'overlays' => '']),
            'post_url' => "/post?id=$id"
        ]);
    }

    // GIFs and videos: animated ones go through ffmpeg into a square MP4 with
    // the overlays burnt into every frame; a single-frame GIF is just a photo.
    private function publishMedia(array $overlays): string
    {
        $file = $_FILES['media'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->fail(['error' => 'No file received']);
        }
        if ($file['size'] > self::MEDIA_MAX_BYTES) {
            $this->fail(['error' => 'File is too large (max 30 MB)']);
        }

        $type = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($type, self::MEDIA_TYPES, true)) {
            $this->fail(['error' => 'Only GIF, MP4, WebM and MOV files are supported']);
        }

        if ($type === 'image/gif' && !self::isAnimatedGif($file['tmp_name'])) {
            $gif = @imagecreatefromgif($file['tmp_name']);
            if (!$gif) {
                $this->fail(['error' => 'Could not read image']);
            }
            return $this->saveComposed(self::framed($gif, self::aspect()), $overlays, 'img_');
        }

        if (!is_dir(self::UPLOAD_DIR) && !mkdir(self::UPLOAD_DIR, 0775, true) && !is_dir(self::UPLOAD_DIR)) {
            $this->fail(['error' => 'Could not prepare upload directory'], 500);
        }
        $name = uniqid('vid_', true) . '.mp4';

        set_time_limit(120);
        exec(self::ffmpegCommand($file['tmp_name'], $overlays, self::aspect(), self::UPLOAD_DIR . "/$name") . ' 2>&1', $output, $code);
        if ($code !== 0) {
            error_log('ffmpeg failed: ' . implode("\n", array_slice($output, -5)));
            @unlink(self::UPLOAD_DIR . "/$name");
            $this->fail(['error' => 'Could not process that file']);
        }

        return self::UPLOAD_URL . "/$name";
    }

    // Same placement rules as stamp(): centre (x, y) and width as fractions of the frame.
    private static function ffmpegCommand(string $input, array $overlays, float $ratio, string $output): string
    {
        $width = self::MEDIA_WIDTH;
        $height = (int) round($width * $ratio);
        $args = ['ffmpeg', '-y', '-i', $input];
        $filters = ["[0:v]crop='min(iw,ih/$ratio)':'min(iw,ih/$ratio)*$ratio',scale=$width:$height,setsar=1,fps=30[b0]"];

        foreach ($overlays as $i => $spec) {
            $path = Overlay::path($spec['id']);
            [$srcW, $srcH] = getimagesize($path);
            $w = max(2, (int) round($spec['size'] * $width));
            $h = max(2, (int) round($w * $srcH / $srcW));
            $angle = $spec['angle'] * M_PI / 180;
            $cx = round($spec['x'] * $width);
            $cy = round($spec['y'] * $height);

            $args[] = '-i';
            $args[] = $path;
            $n = $i + 1;
            $filters[] = "[$n:v]scale=$w:$h,format=rgba,rotate=$angle:c=none:ow=rotw($angle):oh=roth($angle)[o$n]";
            $filters[] = "[b$i][o$n]overlay=x=$cx-overlay_w/2:y=$cy-overlay_h/2[b$n]";
        }

        $last = count($overlays);
        array_push($args,
            '-filter_complex', implode(';', $filters),
            '-map', "[b$last]", '-map', '0:a?',
            '-t', (string) self::MEDIA_MAX_SECONDS,
            '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '26', '-pix_fmt', 'yuv420p',
            '-c:a', 'aac', '-b:a', '128k',
            '-movflags', '+faststart',
            $output
        );

        return implode(' ', array_map('escapeshellarg', $args));
    }

    // More than one graphic control block means more than one frame.
    private static function isAnimatedGif(string $path): bool
    {
        return preg_match_all('#\x00\x21\xF9\x04.{4}\x00[\x2C\x21]#s', (string) file_get_contents($path)) > 1;
    }

    private static function aspect(): float
    {
        return self::ASPECTS[$_POST['aspect'] ?? ''] ?? self::ASPECTS['1:1'];
    }

    // Centre crop to the given height/width ratio, scaled to the standard width.
    private static function framed(\GdImage $source, float $ratio): \GdImage
    {
        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $cropW = (int) min($srcW, $srcH / $ratio);
        $cropH = (int) round($cropW * $ratio);

        $frame = imagecreatetruecolor(self::MEDIA_WIDTH, (int) round(self::MEDIA_WIDTH * $ratio));
        imagecopyresampled(
            $frame, $source, 0, 0,
            intdiv($srcW - $cropW, 2), intdiv($srcH - $cropH, 2),
            imagesx($frame), imagesy($frame), $cropW, $cropH
        );
        imagealphablending($frame, true);
        return $frame;
    }

    private function requireSession(): int
    {
        if (!Session::check()) {
            $this->fail(['error' => 'Session lost'], 403);
        }
        return Session::getId();
    }

    private function overlays(bool $required): array
    {
        $overlays = Overlay::parse($_POST['overlays'] ?? '');
        if ($overlays === null) {
            $this->fail(['overlays' => 'Invalid overlays']);
        }
        if ($required && !$overlays) {
            $this->fail(['overlays' => 'Pick at least one overlay']);
        }
        return $overlays;
    }

    private function ownedDraft(int $id, int $userId): array
    {
        $image = Image::find($id);
        if (!$image || (int) $image['user_id'] !== $userId || !$image['is_draft']) {
            $this->fail(['error' => 'Draft not found'], 404);
        }
        return $image;
    }

    private function uploadedPng(): \GdImage
    {
        $file = $_FILES['image'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->fail(['error' => 'No image received']);
        }
        if ($file['size'] > self::MAX_BYTES) {
            $this->fail(['error' => 'Image is too large']);
        }

        $info = @getimagesize($file['tmp_name']);
        if (!$info || $info[2] !== IMAGETYPE_PNG) {
            $this->fail(['error' => 'Image must be a PNG']);
        }
        if ($info[0] > self::MAX_SIDE || $info[1] > self::MAX_SIDE) {
            $this->fail(['error' => 'Image is too big']);
        }
        $ratio = $info[1] / $info[0];
        if (!array_filter(self::ASPECTS, static fn (float $r) => abs($ratio - $r) < 0.01)) {
            $this->fail(['error' => 'Photos must be square (1:1) or vertical (4:5)']);
        }

        return $this->loadPng($file['tmp_name']);
    }

    private function loadPng(string $path): \GdImage
    {
        $image = @imagecreatefrompng($path);
        if (!$image) {
            $this->fail(['error' => 'Could not read image'], 500);
        }
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        return $image;
    }

    private function saveComposed(\GdImage $base, array $overlays, string $prefix): string
    {
        $width = imagesx($base);
        $height = imagesy($base);

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        imagecopy($image, $base, 0, 0, 0, 0, $width, $height);

        foreach ($overlays as $spec) {
            $this->stamp($image, $this->loadPng(Overlay::path($spec['id'])), $spec);
        }

        return $this->savePng($image, $prefix);
    }

    private function stamp(\GdImage $image, \GdImage $overlay, array $spec): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $srcW = imagesx($overlay);
        $srcH = imagesy($overlay);

        $w = $spec['size'] * $width;
        $h = $w * $srcH / $srcW;

        if ($spec['angle'] != 0) {
            // Rotate at final size; the rotated bounding box is then placed 1:1.
            $sized = imagecreatetruecolor(max(1, (int) round($w)), max(1, (int) round($h)));
            imagealphablending($sized, false);
            imagesavealpha($sized, true);
            imagefill($sized, 0, 0, imagecolorallocatealpha($sized, 0, 0, 0, 127));
            imagecopyresampled($sized, $overlay, 0, 0, 0, 0, imagesx($sized), imagesy($sized), $srcW, $srcH);

            $overlay = imagerotate($sized, -$spec['angle'], imagecolorallocatealpha($sized, 0, 0, 0, 127));
            imagesavealpha($overlay, true);
            $w = $srcW = imagesx($overlay);
            $h = $srcH = imagesy($overlay);
        }

        $x = $spec['x'] * $width - $w / 2;
        $y = $spec['y'] * $height - $h / 2;
        $scale = $srcW / $w;

        $left = max(0, $x);
        $top = max(0, $y);
        $right = min($width, $x + $w);
        $bottom = min($height, $y + $h);
        if ($right - $left < 1 || $bottom - $top < 1) {
            return;
        }

        $cropX = min($srcW - 1, (int) round(($left - $x) * $scale));
        $cropY = min($srcH - 1, (int) round(($top - $y) * $scale));
        $cropW = min($srcW - $cropX, (int) round(($right - $left) * $scale));
        $cropH = min($srcH - $cropY, (int) round(($bottom - $top) * $scale));

        imagecopyresampled(
            $image, $overlay,
            (int) round($left), (int) round($top),
            $cropX, $cropY,
            (int) round($right - $left), (int) round($bottom - $top),
            $cropW, $cropH
        );
    }

    private static function path(string $filename): string
    {
        return __DIR__ . '/../../public' . $filename;
    }

    private function savePng(\GdImage $image, string $prefix): string
    {
        if (!is_dir(self::UPLOAD_DIR) && !mkdir(self::UPLOAD_DIR, 0775, true) && !is_dir(self::UPLOAD_DIR)) {
            $this->fail(['error' => 'Could not prepare upload directory'], 500);
        }

        $name = uniqid($prefix, true) . '.png';
        if (!imagepng($image, self::UPLOAD_DIR . "/$name")) {
            $this->fail(['error' => 'Could not save image'], 500);
        }
        return self::UPLOAD_URL . "/$name";
    }

    private function fail(array $errors, int $status = 400): never
    {
        Helper::json_response(['errors' => $errors], $status);
    }
}
