<?php

namespace App\Models;

use App\Core\Model;

class Follow extends Model
{
    public static function toggle(int $followerId, int $followedId): bool
    {
        $db = self::db();

        $stmt = $db->prepare("DELETE FROM follows WHERE follower_id = :follower AND followed_id = :followed");
        $stmt->execute([':follower' => $followerId, ':followed' => $followedId]);
        if ($stmt->rowCount() > 0) {
            return false;
        }

        $stmt = $db->prepare("INSERT IGNORE INTO follows (follower_id, followed_id) VALUES (:follower, :followed)");
        $stmt->execute([':follower' => $followerId, ':followed' => $followedId]);
        return true;
    }

    // True only the first time for this pair, so re-following doesn't email again.
    public static function markNotified(int $followerId, int $followedId): bool
    {
        $stmt = self::db()->prepare("INSERT IGNORE INTO follow_notifications (follower_id, followed_id) VALUES (:follower, :followed)");
        $stmt->execute([':follower' => $followerId, ':followed' => $followedId]);
        return $stmt->rowCount() > 0;
    }

    public static function isFollowing(int $followerId, int $followedId): bool
    {
        $stmt = self::db()->prepare("SELECT 1 FROM follows WHERE follower_id = :follower AND followed_id = :followed");
        $stmt->execute([':follower' => $followerId, ':followed' => $followedId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function counts(int $userId): array
    {
        $stmt = self::db()->prepare(
            "SELECT
                (SELECT COUNT(*) FROM follows WHERE followed_id = :a) AS followers,
                (SELECT COUNT(*) FROM follows WHERE follower_id = :b) AS following"
        );
        $stmt->execute([':a' => $userId, ':b' => $userId]);
        return array_map('intval', $stmt->fetch(\PDO::FETCH_ASSOC));
    }
}
