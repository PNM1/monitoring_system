<?php

require_once 'security.php';

$security = new Security();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Отсутствует логин или пароль']);
        exit();
    }
    $result = $security->authenticate($input['username'], $input['password']);
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Неразрешенный метод запроса']);
}
