<?php

namespace App\Models;

use App\Core\Model;

class PasswordReset extends Model
{
    public static function find(?int $userId = null, ?string $token = null)
    {
        if ($userId !== null)
            return self::findByUserId($userId);
        if ($token !== null)
            return self::findByToken($token);
        return null;
    }

    public static function findByUserId(int $userId)
    {
        $stmt = self::db()->prepare("SELECT * FROM password_resets WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function findByToken(string $token)
    {
        $stmt = self::db()->prepare("SELECT * FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // A user can only have one active reset request at a time: requesting a
    // new one replaces the previous token instead of failing on the
    // user_id primary key.
    public static function add(array $data): bool
    {
        try {
            $stmt = self::db()->prepare(
                "INSERT INTO password_resets (user_id, token, expires_at)
                 VALUES (:user_id, :token, :expires_at)
                 ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires_at"
            );
            $stmt->execute($data);
            return true;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function deleteByToken(string $token): bool
    {
        try {
            $stmt = self::db()->prepare("DELETE FROM password_resets WHERE token = :token");
            $stmt->execute([':token' => $token]);
            return true;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
