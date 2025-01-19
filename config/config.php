<?php
class Database {
    private static $instance = null;
    private $pdo;

    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {
        $host = "localhost";
        $db_name = "youdemy";
        $username = "root";
        $password = "";

        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$db_name",
                $username,
                $password
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log("Database connection established.");
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed.");
        }
    }

    // Méthode statique pour obtenir l'instance unique
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Méthode pour obtenir la connexion PDO
    public function getConnection() {
        return $this->pdo;
    }

    // Méthode pour assurer la compatibilité avec l'ancien code
    public function connect() {
        return $this->getConnection();
    }
}
?>