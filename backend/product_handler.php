<?php

require_once 'db_handler.php';

class ProductHandler
{
    private $db;

    public function __construct()
    {
        $this->db = new DBHandler();
    }

    public function updateShopFloor($productId, $newLocation = null, $department = null, $row = null, $shelf = null)
    {
        if ($newLocation) {
            $parts = explode(';', $newLocation);
            if (count($parts) === 3) {
                $department = intval($parts[0]);
                $row = intval($parts[1]);
                $shelf = intval($parts[2]);
            } else {
                return ['success' => false, 'message' => 'Неверный формат местаположения'];
            }
        }

        if ($department !== null && $row !== null && $shelf !== null) {
            $result = $this->db->updateProductLocation($productId, $department, $row, $shelf);
            return ['success' => $result, 'message' => $result ? 'Месположение обновлено' : 'Не обновлено'];
        }

        return ['success' => false, 'message' => 'Информация о местоположении потеряна'];
    }
}
