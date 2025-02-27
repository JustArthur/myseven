<?php

    require_once __DIR__ . '/vendor/autoload.php';

    use Dotenv\Dotenv;

    class ConnexionDB {
        private $pdo;

        public function __construct() {
            $dotenv = Dotenv::createImmutable(paths: __DIR__);
            $dotenv->load();

            $dbHost = $_ENV['DB_HOST'];
            $dbName = $_ENV['DB_NAME'];
            $dbUser = $_ENV['DB_USER'];
            $dbPassword = $_ENV['DB_PASSWORD'];

            try {
                $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
                $this->pdo = new PDO(dsn: $dsn, username: $dbUser, password: $dbPassword);

                $this->pdo->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(attribute: PDO::ATTR_DEFAULT_FETCH_MODE, value: PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                echo "Erreur de connexion : " . $e->getMessage();
                exit;
            }
        }

        public function DB() {
            return $this->pdo;
        }

        public function closeConnection() {
            $this->pdo = null;
        }
    }
?>