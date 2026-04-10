<?php

require_once 'db_handler.php';
require_once 'security.php';

session_start();

$security = new Security();
$db = new DBHandler();

if (!isset($_SESSION['user']) || !$security->isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен', 'user' => $_SESSION['user'],
    'is' => !$security->isAdmin()]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $users = $db->getAllUsers();
        echo json_encode(['success' => true, 'users' => $users]);
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['username']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Не указаны логин или пароль']);
            exit();
        }

        $username = trim($input['username']);
        $password = $input['password'];

        if (strlen($username) < 3) {
            echo json_encode(['success' => false, 'message' => 'Логин должен быть не менее 3 символов']);
            exit();
        }
        if (strlen($password) < 3) {
            echo json_encode(['success' => false, 'message' => 'Пароль должен быть не менее 3 символов']);
            exit();
        }

        $result = $db->createUser($username, $password, false);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Пользователь создан']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Пользователь с таким логином уже существует']);
        }
        break;
    case 'DELETE':
        $userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Не указан ID пользователя']);
            exit();
        }

        $result = $db->deleteUser($userId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Пользователь удален']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Не удалось удалить пользователя (возможно, admin)']);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Неразрешенный метод запроса']);
        break;
}
