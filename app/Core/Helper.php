<?php

namespace App\Core;

class Helper {
    static public function json_response($data = null, $httpStatus = 200) {
		header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpStatus);
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        exit();
    }

    static public function avatar(?string $avatarUrl, string $username): string {
        if ($avatarUrl) {
            return '<img src="' . htmlspecialchars($avatarUrl) . '" alt="" class="h-full w-full object-cover">';
        }
        return htmlspecialchars(strtoupper(substr(trim($username), 0, 2)) ?: '??');
    }
}

?>