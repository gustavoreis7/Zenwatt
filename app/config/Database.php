<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'zenwatt';
    private $username = 'root';
    private $password = '';
    public $pdo; // Mudei de $conn para $pdo para coincidir com seu login

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->pdo = null;
        try {
            $this->pdo = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->pdo->exec("set names utf8");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Erro de conexão: " . $exception->getMessage());
        }
    }

    // Método getConnection para compatibilidade
    public function getConnection() {
        return $this->pdo;
    }
}
?>