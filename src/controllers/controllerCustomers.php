<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    header("Content-Type: application/json");

    session_start();

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    }

    require_once '../../database.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data["oldUniqueValue"])) {
        echo json_encode(["error" => "Données invalides", "data" => $data]);
        exit;
    }

    $oldEmail = $data["oldUniqueValue"];
    $newEmail = $data["clients_email"];

    $fields = ["clients_nom", "clients_prenom", "clients_email", "clients_telephone", "clients_adresse", "clients_ville", "clients_cp", "clients_numero_cni"];
    $updateFields = [];
    $params = [];

    if(filter_var($newEmail, FILTER_VALIDATE_EMAIL) === false) {
        echo json_encode(["error" => "Email invalide"]);
        exit;
    }

    if ($newEmail !== $oldEmail) {
        $checkEmail = $DB->prepare("SELECT COUNT(*) FROM clients WHERE clients_email = ?");
        $checkEmail->execute([$newEmail]);
        
        if ($checkEmail->fetchColumn() > 0) {
            echo json_encode(["error" => "Cet email est déjà utilisé."]);
            exit;
        }
    }

    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    $updateFields[] = "clients_email = ?";
    $params[] = $newEmail;

    $params[] = $oldEmail;

    if (!empty($updateFields)) {
        $sql = "UPDATE clients SET " . implode(", ", $updateFields) . " WHERE clients_email = ?";
        $stmt = $DB->prepare($sql);
        
        if ($stmt->execute($params)) {
            echo json_encode(["success" => "Mise à jour réussie"]);
            $DBB->closeConnection();
            exit;

        } else {
            echo json_encode(["error" => "Erreur lors de la mise à jour"]);
            $DBB->closeConnection();
            exit;
        }
    }
?>