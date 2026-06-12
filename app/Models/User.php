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

    public static function find(int $id)
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function findByUsername(string $username) {
        $stmt = self::db()->prepare("SELECT id, username, password FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
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
    public static function getCol(PDOStatement $stmt, string $col, &$data) {
        return $stmt->bindColumn($col,  $data);
    }
}
