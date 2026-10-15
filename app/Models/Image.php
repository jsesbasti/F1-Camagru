<?php

namespace App\Models;

use App\Core\Model;

class Image extends Model
{
    public static function feedPage(int $limit = 4, int $offset = 0, int $viewerId = 0): array
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
                u.avatar,
                COALESCE(l.likes_count, 0) AS likes_count,
                COALESCE(c.comments_count, 0) AS comments_count,
                (my.id IS NOT NULL) AS liked_by_me
            FROM images i
            INNER JOIN users u ON u.id = i.user_id
            LEFT JOIN likes my ON my.image_id = i.id AND my.user_id = :viewer_id
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
            WHERE i.is_draft = 0
            ORDER BY i.created_at DESC, i.id DESC
            LIMIT :limit_plus_one OFFSET :offset"
        );

        $stmt->bindValue(':viewer_id', $viewerId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit_plus_one', $limit + 1, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $hasMore = count($rows) > $limit;

        if ($hasMore) {
            array_pop($rows);
        }

        $commentsByImage = Comment::threadsFor(array_column($rows, 'id'), 3);

        $posts = array_map(static function (array $row) use ($commentsByImage) {
            $imageId = (int) $row['id'];

            return [
                'id' => $imageId,
                'user_id' => (int) $row['user_id'],
                'username' => $row['username'],
                'avatar_initials' => self::initials($row['username']),
                'avatar_url' => $row['avatar'],
                'image_url' => self::imageUrl($row['filename'] ?? ''),
                'caption' => trim((string) ($row['description'] ?? '')),
                'likes_count' => (int) $row['likes_count'],
                'comments_count' => (int) $row['comments_count'],
                'liked_by_me' => (bool) $row['liked_by_me'],
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
            WHERE i.user_id = :user_id AND i.is_draft = 0
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
    public static function findPostById(int $id, int $viewerId = 0): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT
                i.id,
                i.user_id,
                i.filename,
                i.description,
                i.created_at,
                u.username,
                u.avatar,
                COALESCE(l.likes_count, 0) AS likes_count,
                (my.id IS NOT NULL) AS liked_by_me
            FROM images i
            INNER JOIN users u ON u.id = i.user_id
            LEFT JOIN likes my ON my.image_id = i.id AND my.user_id = :viewer_id
            LEFT JOIN (
                SELECT image_id, COUNT(*) AS likes_count
                FROM likes
                GROUP BY image_id
            ) l ON l.image_id = i.id
            WHERE i.id = :id AND i.is_draft = 0"
        );
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':viewer_id', $viewerId, \PDO::PARAM_INT);
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
            'avatar_url' => $row['avatar'],
            'image_url' => self::imageUrl($row['filename'] ?? ''),
            'caption' => trim((string) ($row['description'] ?? '')),
            'likes_count' => (int) $row['likes_count'],
            'liked_by_me' => (bool) $row['liked_by_me'],
            'created_at' => $row['created_at'],
            'badge' => 'Post',
            'comments' => Comment::threadsFor([(int) $row['id']])[(int) $row['id']] ?? [],
        ];
    }

    public static function forStudio(int $userId): array
    {
        $stmt = self::db()->prepare(
            "SELECT id, filename, source_filename, is_draft, overlays
            FROM images
            WHERE user_id = :user_id
            ORDER BY created_at DESC, id DESC"
        );
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return array_map([self::class, 'studioEntry'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public static function studioEntry(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'image_url' => self::imageUrl($row['filename'] ?? ''),
            'is_draft' => (bool) $row['is_draft'],
            'source_url' => self::imageUrl($row['source_filename'] ?? $row['filename'] ?? ''),
            'overlays' => Overlay::parse($row['overlays'] ?? null) ?? [],
        ];
    }

    public static function updateDraft(int $id, string $filename, string $sourceFilename, array $overlays): bool
    {
        $stmt = self::db()->prepare(
            "UPDATE images
            SET filename = :filename, source_filename = :source_filename, overlays = :overlays
            WHERE id = :id AND is_draft = 1"
        );
        return $stmt->execute([
            ':filename' => $filename,
            ':source_filename' => $sourceFilename,
            ':overlays' => json_encode($overlays),
            ':id' => $id
        ]);
    }

    public static function publishDraft(int $id, string $filename, string $description): bool
    {
        $stmt = self::db()->prepare(
            "UPDATE images
            SET filename = :filename, description = :description, is_draft = 0, overlays = NULL, source_filename = NULL, created_at = NOW()
            WHERE id = :id AND is_draft = 1"
        );
        return $stmt->execute([':filename' => $filename, ':description' => $description, ':id' => $id]);
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

    public static function filenamesForUser(int $userId): array
    {
        $stmt = self::db()->prepare(
            "SELECT filename FROM images WHERE user_id = :user_id
            UNION
            SELECT source_filename FROM images WHERE user_id = :user_id2 AND source_filename IS NOT NULL"
        );
        $stmt->execute([':user_id' => $userId, ':user_id2' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            "INSERT INTO images (user_id, filename, description, is_draft, overlays, source_filename)
             VALUES (:user_id, :filename, :description, :is_draft, :overlays, :source_filename)"
        );

        $stmt->bindValue(':user_id', $data['user_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':filename', $data['filename'] ?? '', \PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'] ?? '', \PDO::PARAM_STR);
        $stmt->bindValue(':is_draft', (int) ($data['is_draft'] ?? 0), \PDO::PARAM_INT);
        $stmt->bindValue(':overlays', $data['overlays'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':source_filename', $data['source_filename'] ?? null, \PDO::PARAM_STR);
        $stmt->execute();

        return (int) self::db()->lastInsertId();
    }

    private static function initials(string $username): string
    {
        $cleanName = trim($username);

        return $cleanName === '' ? '??' : strtoupper(substr($cleanName, 0, 2));
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
