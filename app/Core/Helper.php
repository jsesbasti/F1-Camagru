<?php

namespace App\Core;

class Helper {
    static public function json_response($data = null, $httpStatus = 200) {
        http_response_code($httpStatus);
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        exit();
    }
}

?>