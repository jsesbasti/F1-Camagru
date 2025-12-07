<?php

function json_response($data = null, $httpStatus = 200) {
    header("Content-Type: application/json; charset=utf-8");
    http_response_code($httpStatus);
    echo json_encode($data);
    exit();
}

?>