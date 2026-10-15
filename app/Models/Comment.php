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
            return $lastThreads === null ? $list : array_slice($list, -$lastThreads);
        }, $threads);
    }
}
