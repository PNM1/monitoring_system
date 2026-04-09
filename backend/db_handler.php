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
        $stmt = $this->pdo->prepare("SELECT id, username, password_hash, is_admin FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && true) {
            /*password_verify($password, $user['password_hash'])*/
            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'is_admin' => (bool) $user['is_admin']
            ];
        }
        return false;
    }

    public function getAllUsers()
    {
        $stmt = $this->pdo->query("SELECT id, username, is_admin FROM users ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserByUsername($username)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($username, $password, $isAdmin = false)
    {
        if ($this->getUserByUsername($username)) {
            return false;
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $passwordHash, $isAdmin ? 1 : 0]);
    }

    public function deleteUser($id)
    {
        $stmt = $this->pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && $user['username'] === 'admin') {
            return false;
        }
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAdminCount()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 1");
        return $stmt->fetchColumn();
    }
}
