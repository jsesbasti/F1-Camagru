<?php

namespace App\Models;

use App\Core\Model;
use PDOStatement;

class Verification extends Model
{
    public static function all()
    {
        $stmt = self::db()->query("SELECT * FROM email_verification");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function find(?int $id = null, ?string $username = null, ?string $token = null) {
        if ($id !== null)
            return self::findId($id);
        if ($username !== null)
            return self::findByUsername($username);
        if ($token !== null)
            return self::findByToken($token);
        return null;
    }

    public static function findId(int $id)
    {
        $stmt = self::db()->prepare("SELECT * FROM email_verification WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

	public static function findByUsername(string $username) {
		$stmt = self::db()->prepare("SELECT id, email FROM users WHERE username = :username");
		$stmt->execute([":username" => $username]);
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

    public static function findByToken(string $token) {
		$stmt = self::db()->prepare("SELECT * FROM email_verification WHERE token = :token");
		$stmt->execute([":token" => $token]);
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

    public static function add(array $data) {
        try {
            $stmt = self::db()->prepare("INSERT INTO email_verification (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
            $stmt->execute($data);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public static function mod(int $id, string $col, $data) {
        try {
            $stmt = self::db()->prepare("UPDATE email_verification SET :col = :data WHERE id = :id");
            $stmt = execute([':col' => $col, ':data' => $data, ':id' => $id]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public static function getCol(PDOStatement $stmt, string $col, &$data) {
        return $stmt->bindColumn($col,  $data);
    }
}
