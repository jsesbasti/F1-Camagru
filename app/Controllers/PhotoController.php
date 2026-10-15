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

        $base = $draft
            ? $this->loadPng(self::path($draft['source_filename'] ?? $draft['filename']))
            : $this->uploadedPng();

        $filename = $this->saveComposed($base, $overlays, 'img_');

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
