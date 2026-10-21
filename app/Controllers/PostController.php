<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Core\Mailer;
use App\Core\Session;
use App\Models\Image;
use App\Models\Like;
use App\Models\Comment;
use App\Models\User;

class PostController extends Controller
{
    // Single-post page (the photo-grid on profile.php links here) — reuses
    // partials/post-card.php, the same one the feed and infinite scroll use.
    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $currentUser = Session::check() ? User::find(id: Session::getId()) : null;
        $post = $id ? Image::findPostById($id, (int) ($currentUser['id'] ?? 0)) : null;

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

        $feed = Image::feedPage($limit, $offset, Session::check() ? Session::getId() : 0);

        Helper::json_response([
            'posts' => $feed['posts'],
            'hasMore' => $feed['hasMore'],
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    // Polled by home.js every few seconds: current likes for the posts on
    // screen plus any comments added after the last one the page has seen.
    // Without `since` it only returns the newest comment id to start from.
    public function live(): void
    {
        $ids = array_slice(array_unique(array_filter(array_map('intval', explode(',', (string) ($_GET['ids'] ?? ''))))), 0, 100);
        $viewerId = Session::check() ? Session::getId() : 0;
        $lastId = Comment::lastId();

        $comments = [];
        if (isset($_GET['since'])) {
            $comments = Comment::since($ids, max(0, (int) $_GET['since']));
            if ($comments) {
                $lastId = max($lastId, end($comments)['id']);
            }
        }

        Helper::json_response([
            'likes' => (object) Like::stateFor($ids, $viewerId),
            'comment_counts' => (object) Comment::countsFor($ids),
            'comments' => $comments,
            'last_comment_id' => $lastId,
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
            foreach (array_filter([$image['filename'], $image['source_filename'] ?? null]) as $filename) {
                $path = __DIR__ . '/../../public' . $filename;
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            Helper::json_response(['success' => true]);
        } else {
            Helper::json_response(['errors' => ['error' => 'Could not delete post']], 500);
        }
    }

    public function details(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $post = $id ? Image::findPostById($id, Session::check() ? Session::getId() : 0) : null;
        if (!$post) {
            Helper::json_response(['errors' => ['error' => 'Post not found']], 404);
        }
        Helper::json_response(['post' => $post]);
    }

    public function like(): void
    {
        [$userId, $imageId] = $this->interaction();

        Helper::json_response(['success' => true, ...Like::toggle($userId, $imageId)]);
    }

    public function comment(): void
    {
        [$userId, $imageId, $data, $image] = $this->interaction();

        $comment = trim((string) ($data['comment'] ?? ''));
        if ($comment === '') {
            Helper::json_response(['errors' => ['comment' => 'Comment cannot be empty']], 400);
        }
        if (mb_strlen($comment) > Comment::MAX_LENGTH) {
            Helper::json_response(['errors' => ['comment' => 'Comment is too long']], 400);
        }

        $repliedTo = null;
        $parentId = (int) ($data['parent_id'] ?? 0);
        if ($parentId) {
            $repliedTo = Comment::find($parentId);
            if (!$repliedTo || (int) $repliedTo['image_id'] !== $imageId) {
                Helper::json_response(['errors' => ['error' => 'Comment not found']], 404);
            }
            $parentId = (int) ($repliedTo['parent_id'] ?? $repliedTo['id']);
        }

        $id = Comment::add($userId, $imageId, $comment, $parentId ?: null);
        if (!$id) {
            Helper::json_response(['errors' => ['error' => 'Could not save comment']], 500);
        }

        $author = User::find(id: $userId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'comment' => [
                'id' => $id,
                'parent_id' => $parentId ?: null,
                'username' => $author['username'],
                'comment' => $comment,
            ]
        ], JSON_UNESCAPED_SLASHES);
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        $this->notifyComment($author, $image, $comment, $repliedTo);
        exit;
    }

    private function notifyComment(array $author, array $image, string $comment, ?array $repliedTo): void
    {
        $footer = "\r\n\r\n" . Mailer::link('See the post', "/post?id={$image['id']}")
            . "\r\n\r\nYou can turn these emails off in " . Mailer::link('Settings', '/settings') . ".";
        $authorName = Mailer::e($author['username']);
        $rawComment = $comment;
        $comment = Mailer::e($comment);

        $notified = [(int) $author['id']];

        $owner = User::find(id: (int) $image['user_id']);
        if ($owner && !in_array((int) $owner['id'], $notified, true) && $owner['notify_comments']) {
            Mailer::send($owner['email'], "{$author['username']} commented on your photo",
                "Hi " . Mailer::e($owner['username']) . ",\r\n\r\n$authorName commented on your photo:\r\n\r\n\"$comment\"" . $footer);
        }
        $notified[] = (int) $image['user_id'];

        if ($repliedTo) {
            $target = User::find(id: (int) $repliedTo['user_id']);
            if ($target && !in_array((int) $target['id'], $notified, true) && $target['notify_comments']) {
                Mailer::send($target['email'], "{$author['username']} replied to your comment",
                    "Hi " . Mailer::e($target['username']) . ",\r\n\r\n$authorName replied to your comment \"" . Mailer::e($repliedTo['comment']) . "\":\r\n\r\n\"$comment\"" . $footer);
            }
            $notified[] = (int) $repliedTo['user_id'];
        }

        // @mentions, same pattern as the profile links in post-card.php. Capped so
        // one comment can't be used to mass-email people.
        preg_match_all('/(?<![\w@])@(\w+)/', $rawComment, $matches);
        foreach (array_slice(array_unique($matches[1]), 0, 10) as $username) {
            $mentioned = User::find(username: $username);
            if ($mentioned && !in_array((int) $mentioned['id'], $notified, true) && $mentioned['notify_comments']) {
                Mailer::send($mentioned['email'], "{$author['username']} mentioned you in a comment",
                    "Hi " . Mailer::e($mentioned['username']) . ",\r\n\r\n$authorName mentioned you in a comment:\r\n\r\n\"$comment\"" . $footer);
            }
            if ($mentioned) {
                $notified[] = (int) $mentioned['id'];
            }
        }
    }

    private function interaction(): array
    {
        if (!Session::check()) {
            Helper::json_response(['errors' => ['error' => 'You need to log in'], 'redirect' => '/login'], 401);
        }

        $data = json_decode(file_get_contents("php://input"), true) ?: [];
        $imageId = (int) ($data['id'] ?? 0);

        $image = $imageId ? Image::find($imageId) : null;
        if (!$image || $image['is_draft']) {
            Helper::json_response(['errors' => ['error' => 'Post not found']], 404);
        }

        return [Session::getId(), $imageId, $data, $image];
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
