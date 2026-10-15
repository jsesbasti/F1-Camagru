<?php

namespace App\Models;

use App\Core\Model;
use PDOStatement;

class User extends Model
{
    public static function all()
    {
        $stmt = self::db()->query("SELECT * FROM users");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function find(?int $id = null, ?string $username = null, ?string $email = null) {
        if ($id !== null)
            return self::findId($id);
        if ($username !== null)
            return self::findByUsername($username);
        if ($email !== null)
            return self::findByEmail($email);
        return null;
    }

    public static function findId(int $id)
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function findByUsername(string $username) {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function findByEmail(string $email) {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function add(array $data) {
        try {
            $stmt = self::db()->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute($data);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
        // return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function mod(int $id, string $col, $data): bool
    {
        $allowed = [
            'is_verified',
            'notify_comments',
            'avatar',
            'username',
            'email',
            'password'
        ];

        try {
            if (!in_array($col, $allowed, true)) {
                throw new \InvalidArgumentException('Invalid column');
            }
            $stmt = self::db()->prepare(
                "UPDATE users SET $col = :value WHERE id = :id"
            );
            $stmt->execute([
                'value' => $data,
                'id' => $id
            ]);
            return true;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function getCol(PDOStatement $stmt, string $col, &$data) {
        return $stmt->bindColumn($col,  $data);
    }

    public static function delete(int $id): bool
    {
        try {
            $stmt = self::db()->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
