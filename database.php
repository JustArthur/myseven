<?php

require_once 'vendor/autoload.php';
use Dotenv\Dotenv;

class ConnexionDB {
    private $pdo;

    public function __construct() {
        $dotenvPath = __DIR__ . '/.env';

        if (!file_exists($dotenvPath)) {
            $this->displayError("Erreur de configuration", "Le fichier <strong>.env</strong> est introuvable. Veuillez vérifier son existence.");
        }

        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();

        $dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: $this->displayError("Configuration manquante", "La variable <strong>DB_HOST</strong> est absente ou vide du fichier .env.");
        $dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: $this->displayError("Configuration manquante", "La variable <strong>DB_NAME</strong> est absente ou vide du fichier .env.");
        $dbUser = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: $this->displayError("Configuration manquante", "La variable <strong>DB_USER</strong> est absente ou vide du fichier .env.");
        $dbPassword = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: $this->displayError("Configuration manquante", "La variable <strong>DB_PASSWORD</strong> est absente ou vide du fichier .env.");

        if (!$dbUser) {
            $this->displayError("Erreur critique", "Le champ <strong>DB_USER</strong> est vide. Vérifiez votre configuration.");
        }

        try {
            $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
            $options = [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $dbUser, $dbPassword, $options);

        } catch (PDOException $e) {
            $this->displayError("Erreur de connexion", "Impossible de se connecter à la base de données.<br><strong>Détails :</strong> " . htmlspecialchars($e->getMessage()));
        }
    }

    private function logError($message) {
        $logFile = __DIR__ . '/error.log';
        $date = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$date] $message" . PHP_EOL, FILE_APPEND);
    }

    private function displayError($title, $message) {
        $this->logError("$title : $message");
        die('
            <div style="max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ff4d4d; background-color: #ffe6e6; border-radius: 8px; text-align: center; font-family: Arial, sans-serif;">
                <h2 style="color: #cc0000;">' . $title . '</h2>
                <p style="color: #333;">' . $message . '</p>
            </div>
        ');
    }

    public function openConnection() {
        return $this->pdo;
    }

    public function closeConnection() {
        $this->pdo = null;
    }
}

?>