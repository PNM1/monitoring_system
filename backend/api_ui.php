<?php

declare(strict_types=1);

require_once 'query_module.php';
require_once 'product_handler.php';

$queryModule = new QueryModule();
$productHandler = new ProductHandler();

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';

switch ($method) {
    case 'GET':
        if ($path === '/products') {
            $db = new DBHandler();
            $products = $db->getAllProducts();
            echo json_encode(['success' => true, 'products' => $products]);
        } elseif (preg_match('/\/products\/(\d+)/', $path, $matches)) {
            $result = $queryModule->checkProductAvailability($matches[1]);
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Товар не найден']);
        }
        break;
    case 'POST':
        if ($path === '/restock') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input && $_POST) {
                $input = $_POST;
            }
            if (!$input) {
                $rawInput = file_get_contents('php://input');
                $utf8String = mb_convert_encoding($rawInput, 'UTF-8', ['UTF-8', 'Windows-1251', 'KOI8-R']);
                $input = json_decode($utf8String, true);
            }
            if (isset($input['product_name'])) {
                $productName = trim($input['product_name'], " \t\n\r\0\x0B\xEF\xBB\xBF");
                $result = $queryModule->decreaseWarehouseStock($productName);
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Требуется название товара']);
            }
        } elseif ($path === '/sell') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input && $_POST) {
                $input = $_POST;
            }

            $required = ['product_name', 'color', 'size', 'quantity'];
            $missing = array_diff($required, array_keys($input));
            if (!empty($missing)) {
                http_response_code(400);
                echo json_encode(['error' => 'Отсутствуют поля: ' . implode(', ', $missing)]);
                break;
            }

            $result = $queryModule->sellProduct(
                $input['product_name'],
                $input['color'],
                $input['size'],
                (int) $input['quantity']
            );

            if (!$result['success']) {
                http_response_code(400);
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        }
        break;
    case 'PUT':
        if (preg_match('/\/products\/(\d+)\/location/', $path, $matches)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $productId = $matches[1];

            if (isset($input['location'])) {
                $result = $productHandler->updateShopFloor($productId, $input['location']);
            } elseif (isset($input['department']) && isset($input['row']) && isset($input['shelf'])) {
                $result = $productHandler->updateShopFloor($productId, null, $input[
                    'department'], $input['row'], $input['shelf']);
            } else {
                $result = ['success' => false, 'message' => 'Не введена информация о месте товара'];
            }

            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Товар не найден']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Неправильный способ доступа']);
        break;
}
