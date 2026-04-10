<?php

require_once 'db_handler.php';

class Security
{
    private $db;

    public function __construct()
    {
        $this->db = new DBHandler();
    }

    public function authenticate($username, $password)
    {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Логин и пароль не должны быть пустыми'];
        }

        $result = $this->db->authenticateUser($username, $password);

        if ($result) {
            session_start();
            $_SESSION['user'] = $username;
            $_SESSION['is_admin'] = $result['is_admin'];
            return [
                'success' => true,
                'message' => 'Аутентификация прошла успешно',
                'is_admin' => $result['is_admin']
            ];
        }

        return ['success' => false, 'message' => 'Неправильный логин или пароль'];
    }

    public function isAdmin()
    {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
}
