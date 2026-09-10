<?php

namespace App\Models;

use App\Core\Model;

class Image extends Model
{
    public static function feedPage(int $limit = 4, int $offset = 0): array
    {
        $limit = max(1, min($limit, 20));
        $offset = max(0, $offset);

        $stmt = self::db()->prepare(
            "SELECT
                i.id,
                i.user_id,
                i.filename,
                i.description,
                i.created_at,
                u.username,
                COALESCE(l.likes_count, 0) AS likes_count,
                COALESCE(c.comments_count, 0) AS comments_count
            FROM images i
            INNER JOIN users u ON u.id = i.user_id
            LEFT JOIN (
                SELECT image_id, COUNT(*) AS likes_count
                FROM likes
                GROUP BY image_id
            ) l ON l.image_id = i.id
            LEFT JOIN (
                SELECT image_id, COUNT(*) AS comments_count
                FROM comments
                GROUP BY image_id
            ) c ON c.image_id = i.id
            ORDER BY i.created_at DESC, i.id DESC
            LIMIT :limit_plus_one OFFSET :offset"
        );

        $stmt->bindValue(':limit_plus_one', $limit + 1, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $hasMore = count($rows) > $limit;

        if ($hasMore) {
            array_pop($rows);
        }

        $commentsByImage = self::commentsForImages(array_column($rows, 'id'));

        $posts = array_map(static function (array $row) use ($commentsByImage) {
            $imageId = (int) $row['id'];

            return [
                'id' => $imageId,
                'user_id' => (int) $row['user_id'],
                'username' => $row['username'],
                'avatar_initials' => self::initials($row['username']),
                'image_url' => self::imageUrl($row['filename'] ?? ''),
                'caption' => trim((string) ($row['description'] ?? '')),
                'likes_count' => (int) $row['likes_count'],
                'comments_count' => (int) $row['comments_count'],
                'created_at' => $row['created_at'],
                'badge' => 'Post',
                'comments' => $commentsByImage[$imageId] ?? [],
            ];
        }, $rows);

        return [
            'posts' => $posts,
            'hasMore' => $hasMore,
        ];
    }

    public static function forUser(int $userId): array
    {
        $stmt = self::db()->prepare(
            "SELECT
                i.id,
                i.filename,
                i.description,
                i.created_at,
                COALESCE(l.likes_count, 0) AS likes_count,
                COALESCE(c.comments_count, 0) AS comments_count
            FROM images i
            LEFT JOIN (
                SELECT image_id, COUNT(*) AS likes_count
                FROM likes
                GROUP BY image_id
            ) l ON l.image_id = i.id
            LEFT JOIN (
                SELECT image_id, COUNT(*) AS comments_count
                FROM comments
                GROUP BY image_id
            ) c ON c.image_id = i.id
            WHERE i.user_id = :user_id
            ORDER BY i.created_at DESC, i.id DESC"
        );

        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(static function (array $row) {
            return [
                'id' => (int) $row['id'],
                'image_url' => self::imageUrl($row['filename'] ?? ''),
                'caption' => trim((string) ($row['description'] ?? '')),
                'likes_count' => (int) $row['likes_count'],
                'comments_count' => (int) $row['comments_count'],
                'created_at' => $row['created_at'],
            ];
        }, $rows);
    }

    // Single post, shaped exactly like one feedPage() entry (minus
    // comments_count, and comments in full instead of capped at 3) so it
    // can be handed straight to partials/post-card.php for the /post page.
    public static function findPostById(int $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT
                i.id,
                i.user_id,
                i.filename,
                i.description,
                i.created_at,
                u.username,
                COALESCE(l.likes_count, 0) AS likes_count
            FROM images i
            INNER JOIN users u ON u.id = i.user_id
            LEFT JOIN (
                SELECT image_id, COUNT(*) AS likes_count
                FROM likes
                GROUP BY image_id
            ) l ON l.image_id = i.id
            WHERE i.id = :id"
        );
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'user_id' => (int) $row['user_id'],
            'username' => $row['username'],
            'avatar_initials' => self::initials($row['username']),
            'image_url' => self::imageUrl($row['filename'] ?? ''),
            'caption' => trim((string) ($row['description'] ?? '')),
            'likes_count' => (int) $row['likes_count'],
            'created_at' => $row['created_at'],
            'badge' => 'Post',
            'comments' => self::allCommentsForImage((int) $row['id']),
        ];
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM images WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function updateDescription(int $id, string $description): bool
    {
        try {
            $stmt = self::db()->prepare("UPDATE images SET description = :description WHERE id = :id");
            $stmt->execute([':description' => $description, ':id' => $id]);
            return true;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function delete(int $id): bool
    {
        try {
            $stmt = self::db()->prepare("DELETE FROM images WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return true;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            "INSERT INTO images (user_id, filename, description)
             VALUES (:user_id, :filename, :description)"
        );

        $stmt->bindValue(':user_id', $data['user_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':filename', $data['filename'] ?? '', \PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'] ?? '', \PDO::PARAM_STR);
        $stmt->execute();

        return (int) self::db()->lastInsertId();
    }

    private static function commentsForImages(array $imageIds): array
    {
        if (empty($imageIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = self::db()->prepare(
            "SELECT
                c.image_id,
                c.comment,
                c.created_at,
                u.username
            FROM comments c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.image_id IN ($placeholders)
            ORDER BY c.created_at ASC, c.id ASC"
        );
        $stmt->execute(array_values($imageIds));

        $commentsByImage = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $imageId = (int) $row['image_id'];

            if (!isset($commentsByImage[$imageId])) {
                $commentsByImage[$imageId] = [];
            }

            if (count($commentsByImage[$imageId]) >= 3) {
                continue;
            }

            $commentsByImage[$imageId][] = [
                'username' => $row['username'],
                'comment' => $row['comment'],
                'created_at' => $row['created_at'],
            ];
        }

        return $commentsByImage;
    }

    // Same shape as one commentsForImages() entry, but unlimited — used by
    // the single-post page instead of the feed's 3-comment preview.
    private static function allCommentsForImage(int $imageId): array
    {
        $stmt = self::db()->prepare(
            "SELECT c.comment, c.created_at, u.username
            FROM comments c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.image_id = :image_id
            ORDER BY c.created_at ASC, c.id ASC"
        );
        $stmt->bindValue(':image_id', $imageId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private static function initials(string $username): string
    {
        $cleanName = trim($username);

        if ($cleanName === '') {
            return '??';
        }

        $parts = preg_split('/[^a-zA-Z0-9]+/', $cleanName) ?: [];
        $letters = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $letters .= strtoupper(substr($part, 0, 1));
            if (strlen($letters) >= 2) {
                break;
            }
        }

        if ($letters === '') {
            $letters = strtoupper(substr($cleanName, 0, 2));
        }

        return str_pad(substr($letters, 0, 2), 2, '?');
    }

    private static function imageUrl(string $filename): string
    {
        $cleanFilename = trim($filename);

        if ($cleanFilename === '') {
            return '';
        }

        if (preg_match('#^(https?:)?//#i', $cleanFilename) || str_starts_with($cleanFilename, '/')) {
            return $cleanFilename;
        }

        if (str_starts_with($cleanFilename, 'assets/') || str_starts_with($cleanFilename, 'img/')) {
            return $cleanFilename;
        }

        return 'assets/img/' . ltrim($cleanFilename, '/');
    }
}
