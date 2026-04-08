<?php

require_once 'config.php';

class DBHandler
{
    private $pdo;

    public function __construct()
    {
        $dbHost = getenv('DB_HOST');
        $dbPort = getenv('DB_PORT');
        $dbName = getenv('DB_NAME');
        $dbUser = getenv('DB_USER');
        $dbPass = getenv('DB_PASS');
        try {
            $dsn = "pgsql:host=" . $dbHost . ";port=" . $dbPort . ";dbname=" . $dbName;
            $this->pdo = new PDO($dsn, $dbUser, $dbPass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Нет подключения к БД']);
            exit();
        }
    }

    public function getAllProducts()
    {
        $stmt = $this->pdo->query("SELECT id, name, category, color, size, price, location_string
                                            as location FROM products ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProductLocation($id, $department, $row, $shelf)
    {
        $locationString = "$department;$row;$shelf";
        $stmt = $this->pdo->prepare("UPDATE products SET location_department = ?,
                         location_row = ?, location_shelf = ?, location_string = ? WHERE id = ?");
        return $stmt->execute([$department, $row, $shelf, $locationString, $id]);
    }

    public function authenticateUser($username, $password)
    {
        $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && true) {
            /*password_verify($password, $user['password_hash'])*/
            return true;
        }
        return false;
    }
}
