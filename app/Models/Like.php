<?php

namespace App\Models;

use App\Core\Model;

class Like extends Model
{
    public static function toggle(int $userId, int $imageId): array
    {
        $db = self::db();

        $stmt = $db->prepare("DELETE FROM likes WHERE user_id = :user_id AND image_id = :image_id");
        $stmt->execute([':user_id' => $userId, ':image_id' => $imageId]);
        $liked = $stmt->rowCount() === 0;

        if ($liked) {
            $stmt = $db->prepare("INSERT IGNORE INTO likes (user_id, image_id) VALUES (:user_id, :image_id)");
            $stmt->execute([':user_id' => $userId, ':image_id' => $imageId]);
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM likes WHERE image_id = :image_id");
        $stmt->execute([':image_id' => $imageId]);

        return ['liked' => $liked, 'likes_count' => (int) $stmt->fetchColumn()];
    }

    // Like count and the viewer's own like for each image, keyed by image id —
    // what the feed polls to keep its hearts up to date.
    public static function stateFor(array $imageIds, int $viewerId): array
    {
        if (empty($imageIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = self::db()->prepare(
            "SELECT i.id,
                (SELECT COUNT(*) FROM likes l WHERE l.image_id = i.id) AS likes_count,
                EXISTS(SELECT 1 FROM likes my WHERE my.image_id = i.id AND my.user_id = ?) AS liked_by_me
            FROM images i
            WHERE i.is_draft = 0 AND i.id IN ($placeholders)"
        );
        $stmt->execute([$viewerId, ...array_values($imageIds)]);

        $state = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $state[(int) $row['id']] = [
                'likes_count' => (int) $row['likes_count'],
                'liked' => (bool) $row['liked_by_me'],
            ];
        }
        return $state;
    }
}
