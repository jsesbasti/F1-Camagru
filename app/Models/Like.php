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
}
