<?php

require_once 'config.php';
require_once 'db_handler.php';
require_once 'product_handler.php';

class QueryModule
{
    private $db;
    private $productHandler;

    public function __construct()
    {
        $this->db = new DBHandler();
        $this->productHandler = new ProductHandler();
    }

    public function checkProductAvailability($productId)
    {
        $product = $this->db->getProductById($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Товар не найден'];
        }

        $warehouseStock = $this->queryWarehouseSystem($product['name']);

        return [
            'success' => true,
            'product' => $product,
            'warehouse_quantity' => $warehouseStock
        ];
    }

    private function queryWarehouseSystem($productName)
    {
        if (!file_exists(WAREHOUSE_CSV)) {
            return 0;
        }

        $handle = fopen(WAREHOUSE_CSV, 'r');
        fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== false) {
            if (trim($data[0]) === $productName) {
                fclose($handle);
                return intval($data[1]);
            }
        }
        fclose($handle);
        return 0;
    }

    public function decreaseWarehouseStock(string $productName)
    {
        if (!file_exists(WAREHOUSE_CSV)) {
            return ['success' => false, 'message' => 'Файл склада не найден'];
        }

        $rows = [];
        $found = false;
        $newQuantity = 0;

        $handle = fopen(WAREHOUSE_CSV, 'r');
        $headers = fgetcsv($handle);
        while (($data = fgetcsv($handle)) !== false) {
            if (trim($data[0]) === $productName) {
                $currentQuantity = intval($data[1]);
                $newQuantity = max(0, $currentQuantity - 1);
                $data[1] = $newQuantity;
                $found = true;
                $rows[] = $data;
            } else {
                $rows[] = $data;
            }
        }
        fclose($handle);

        if (!$found) {
            return ['success' => false, 'message' => 'Товар не найден на складе ' . $productName];
        }

        $handle = fopen(WAREHOUSE_CSV, 'w');
        fputcsv($handle, $headers);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        return [
            'success' => true,
            'message' => 'Количество товара уменьшено',
            'product_name' => $productName,
            'new_quantity' => $newQuantity
        ];
    }

    public function restockProduct($productId, $quantity)
    {
        $product = $this->db->getProductById($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Товар не найден'];
        }

        $this->updateWarehouseStock($product['name'], $quantity);

        return $this->productHandler->updateShopFloor($productId, null);
    }

    private function updateWarehouseStock($productName, $quantity)
    {
        $rows = [];
        $found = false;

        if (file_exists(WAREHOUSE_CSV)) {
            $handle = fopen(WAREHOUSE_CSV, 'r');
            while (($data = fgetcsv($handle)) !== false) {
                if (trim($data[0]) === $productName) {
                    $data[1] = $quantity;
                    $found = true;
                }
                $rows[] = $data;
            }
            fclose($handle);
        }

        if (!$found) {
            $rows[] = [$productName, $quantity];
        }

        $handle = fopen(WAREHOUSE_CSV, 'w');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }

    public function sellProduct($productName, $color, $size, $quantity)
    {
        $product = $this->db->getProductByNameColorSize($productName, $color, $size);
        if (!$product) {
            return ['success' => false, 'message' => 'Товар с такими параметрами не найден'];
        }
        if ($product['quantity'] < $quantity) {
            return [
                'success' => false,
                'message' => 'Недостаточно товара в торговом зале',
                'available' => $product['quantity'],
                'requested' => $quantity
            ];
        }
        $result = $this->db->decreaseProductQuantity($product['id'], $quantity);
        if (!$result['success']) {
            return $result;
        }
        return [
            'success' => true,
            'message' => 'Продажа выполнена успешно',
            'product_id' => $product['id'],
            'remaining_in_shop' => $result['new_quantity']
        ];
    }
}
