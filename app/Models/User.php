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

    // Verified users whose name contains $query, names starting with it first.
    public static function search(string $query, int $limit = 20): array
    {
        $like = addcslashes($query, '\\%_');
        $stmt = self::db()->prepare(
            "SELECT id, username, avatar
            FROM users
            WHERE is_verified = 1 AND username LIKE :contains
            ORDER BY username LIKE :prefix DESC, username ASC
            LIMIT :limit"
        );
        $stmt->bindValue(':contains', "%$like%");
        $stmt->bindValue(':prefix', "$like%");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Users ordered by their most recent published photo.
    public static function latestPosters(int $limit): array
    {
        $stmt = self::db()->prepare(
            "SELECT u.id, u.username, u.avatar
            FROM users u
            INNER JOIN images i ON i.user_id = u.id AND i.is_draft = 0
            GROUP BY u.id
            ORDER BY MAX(i.created_at) DESC, MAX(i.id) DESC
            LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
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
            // parent_id has no FK, so other people's replies to this user's comments go by hand.
            self::db()->prepare('DELETE r FROM comments r JOIN comments p ON r.parent_id = p.id WHERE p.user_id = :id')
                ->execute([':id' => $id]);
            $stmt = self::db()->prepare('DELETE FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
