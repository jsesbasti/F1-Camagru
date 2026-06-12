<?php

namespace App\Models;

header("Content-Type: application/json; charset=utf-8");
function json_response($data = null, $httpStatus = 200) {
    http_response_code($httpStatus);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit();
}

?>