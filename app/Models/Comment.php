<?php

namespace App\Models;

use App\Core\Model;

class Comment extends Model
{
    public const MAX_LENGTH = 500;

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM comments WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function add(int $userId, int $imageId, string $comment, ?int $parentId = null): int
    {
        try {
            $stmt = self::db()->prepare(
                "INSERT INTO comments (user_id, image_id, parent_id, comment)
                 VALUES (:user_id, :image_id, :parent_id, :comment)"
            );
            $stmt->execute([
                ':user_id' => $userId,
                ':image_id' => $imageId,
                ':parent_id' => $parentId,
                ':comment' => $comment
            ]);
            return (int) self::db()->lastInsertId();
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public static function threadsFor(array $imageIds, ?int $lastThreads = null): array
    {
        if (empty($imageIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = self::db()->prepare(
            "SELECT c.id, c.image_id, c.parent_id, c.comment, c.created_at, u.username
            FROM comments c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.image_id IN ($placeholders)
            ORDER BY c.created_at ASC, c.id ASC"
        );
        $stmt->execute(array_values($imageIds));

        $threads = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $imageId = (int) $row['image_id'];
            $entry = [
                'id' => (int) $row['id'],
                'parent_id' => $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
                'username' => $row['username'],
                'comment' => $row['comment'],
                'created_at' => $row['created_at'],
            ];

            if ($entry['parent_id'] === null) {
                $threads[$imageId][$entry['id']] = $entry + ['replies' => []];
            } elseif (isset($threads[$imageId][$entry['parent_id']])) {
                $threads[$imageId][$entry['parent_id']]['replies'][] = $entry;
            }
        }

        return array_map(static function (array $byId) use ($lastThreads) {
            $list = array_values($byId);
            if ($lastThreads === null) {
                return $list;
            }
            // Feed preview: newest threads, each with only its first reply.
            return array_map(
                static fn (array $thread) => ['replies' => array_slice($thread['replies'], 0, 1)] + $thread,
                array_slice($list, -$lastThreads)
            );
        }, $threads);
    }

    // [image_id => number of comments, replies included] for these images.
    public static function countsFor(array $imageIds): array
    {
        $counts = array_fill_keys($imageIds, 0);
        if (empty($imageIds)) {
            return $counts;
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = self::db()->prepare("SELECT image_id, COUNT(*) FROM comments WHERE image_id IN ($placeholders) GROUP BY image_id");
        $stmt->execute(array_values($imageIds));
        foreach ($stmt->fetchAll(\PDO::FETCH_KEY_PAIR) as $imageId => $count) {
            $counts[$imageId] = (int) $count;
        }
        return $counts;
    }

    public static function lastId(): int
    {
        return (int) self::db()->query("SELECT COALESCE(MAX(id), 0) FROM comments")->fetchColumn();
    }

    // Comments newer than $sinceId on the given images, oldest first, so the
    // feed can append what other people wrote since its last poll.
    public static function since(array $imageIds, int $sinceId): array
    {
        if (empty($imageIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = self::db()->prepare(
            "SELECT c.id, c.image_id, c.parent_id, c.comment, c.created_at, u.username
            FROM comments c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.id > ? AND c.image_id IN ($placeholders)
            ORDER BY c.id ASC"
        );
        $stmt->execute([$sinceId, ...array_values($imageIds)]);

        return array_map(static fn (array $row) => [
            'id' => (int) $row['id'],
            'image_id' => (int) $row['image_id'],
            'parent_id' => $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
            'username' => $row['username'],
            'comment' => $row['comment'],
            'created_at' => $row['created_at'],
        ], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }
}
