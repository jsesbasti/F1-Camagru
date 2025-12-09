<?php

namespace App\Models;

use App\Core\Model;

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
}
