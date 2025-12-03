<?php

function json_response($data = null, $httpStatus = 200) {
    http_response_code($httpStatus);
    echo json_encode($data);
    exit();
}

?>