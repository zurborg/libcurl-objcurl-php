<?php echo json_encode([
    'SERVER'    => $_SERVER,
    'FILES'     => $_FILES,
    'GET'       => $_GET,
    'POST'      => $_POST,
    'ENV'       => $_ENV,
    'REQUEST'   => $_REQUEST,
    'COOKIE'    => $_COOKIE,
    'input'     => file_get_contents('php://input'),
]);
