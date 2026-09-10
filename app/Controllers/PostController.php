<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Core\Session;
use App\Models\Image;
use App\Models\User;

class PostController extends Controller
{
    // Single-post page (the photo-grid on profile.php links here) — reuses
    // partials/post-card.php, the same one the feed and infinite scroll use.
    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $currentUser = Session::check() ? User::find(id: Session::getId()) : null;
        $post = $id ? Image::findPostById($id) : null;

        $this->view('post', [
            'title' => $post ? $post['username'] . ' · Post' : 'Post not found',
            'currentUser' => $currentUser,
            'post' => $post,
            'scripts' => [
                'home'
            ]
        ]);
    }

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, min((int) ($_GET['limit'] ?? 4), 20));
        $offset = ($page - 1) * $limit;

        $feed = Image::feedPage($limit, $offset);

        Helper::json_response([
            'posts' => $feed['posts'],
            'hasMore' => $feed['hasMore'],
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    public function update(): void
    {
        if (!Session::check()) {
            Helper::json_response(['errors' => ['error' => 'Session lost']], 403);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int) ($data['id'] ?? 0);
        $description = trim((string) ($data['description'] ?? ''));

        if (!$id) {
            Helper::json_response(['errors' => ['error' => 'Missing id']], 400);
            exit;
        }
        if (mb_strlen($description) > 2200) {
            Helper::json_response(['errors' => ['description' => 'Caption is too long']], 400);
            exit;
        }

        $image = self::ownedImage($id);
        if (!$image) {
            Helper::json_response(['errors' => ['error' => 'Post not found']], 404);
            exit;
        }

        if (Image::updateDescription($id, $description)) {
            Helper::json_response(['success' => true, 'caption' => $description]);
        } else {
            Helper::json_response(['errors' => ['error' => 'Could not update post']], 500);
        }
    }

    public function delete(): void
    {
        if (!Session::check()) {
            Helper::json_response(['errors' => ['error' => 'Session lost']], 403);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int) ($data['id'] ?? 0);

        if (!$id) {
            Helper::json_response(['errors' => ['error' => 'Missing id']], 400);
            exit;
        }

        $image = self::ownedImage($id);
        if (!$image) {
            Helper::json_response(['errors' => ['error' => 'Post not found']], 404);
            exit;
        }

        if (Image::delete($id)) {
            $path = __DIR__ . '/../../public' . $image['filename'];
            if (is_file($path)) {
                @unlink($path);
            }
            Helper::json_response(['success' => true]);
        } else {
            Helper::json_response(['errors' => ['error' => 'Could not delete post']], 500);
        }
    }

    // Fetches the image only if it belongs to the signed-in user, so
    // update()/delete() can't touch anyone else's post.
    private static function ownedImage(int $id): ?array
    {
        $image = Image::find($id);
        if (!$image || (int) $image['user_id'] !== Session::getId()) {
            return null;
        }
        return $image;
    }
}
