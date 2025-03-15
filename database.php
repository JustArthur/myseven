<?php

    require_once 'vendor/autoload.php';
    use Dotenv\Dotenv;

    class ConnexionDB {
        private $pdo;

        public function __construct() {
            $dotenv = Dotenv::createImmutable(__DIR__);
            $dotenv->load();

            $dbHost = $_ENV['DB_HOST'];
            $dbName = $_ENV['DB_NAME'];
            $dbUser = $_ENV['DB_USER'];
            $dbPassword = $_ENV['DB_PASSWORD'];

            try {
                //Connexion TLS
                $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
                
                $options = [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                $this->pdo = new PDO($dsn, $dbUser, $dbPassword, $options);

            } catch (PDOException $e) {
                echo "Une erreur est survenue. Veuillez réessayer plus tard.";
                exit;
            }
        }

        public function openConnection() {
            return $this->pdo;
        }

        public function closeConnection() {
            $this->pdo = null;
        }
    }

?>
