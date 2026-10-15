<?php

namespace App\Models;

use App\Core\Model;

class EmailChange extends Model
{
    public static function findByUser(int $userId)
    {
        $stmt = self::db()->prepare("SELECT * FROM email_changes WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function findByToken(string $token)
    {
        $stmt = self::db()->prepare("SELECT * FROM email_changes WHERE token = :token");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function add(array $data): bool
    {
        try {
            $stmt = self::db()->prepare(
                "REPLACE INTO email_changes (user_id, new_email, token, expires_at)
                 VALUES (:user_id, :new_email, :token, :expires_at)"
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
            $stmt = self::db()->prepare("DELETE FROM email_changes WHERE token = :token");
            $stmt->execute([':token' => $token]);
            return true;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
