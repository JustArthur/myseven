<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    header("Content-Type: application/json");
    // header("Access-Control-Allow-Origin: *");
    // header("Access-Control-Allow-Methods: POST");
    // header("Access-Control-Allow-Headers: Content-Type");

    require_once('../../vendor/setasign/fpdi/src/autoload.php');
    require_once("../../connexionDB.php");

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data["oldEmail"])) {
        echo json_encode(["error" => "Données invalides", "data" => $data]);
        exit;
    }

    $oldEmail = $data["oldEmail"];
    $newEmail = $data["email"];

    $fields = ["nom", "prenom", "email", "telephone", "adresse", "ville", "cp", "numero_cni"];
    $updateFields = [];
    $params = [];

    if ($newEmail !== $oldEmail) {
        $checkEmail = $DB->prepare("SELECT COUNT(*) FROM Clients WHERE email = ?");
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

    $updateFields[] = "email = ?";
    $params[] = $newEmail;

    $params[] = $oldEmail;

    if (!empty($updateFields)) {
        $sql = "UPDATE Clients SET " . implode(", ", $updateFields) . " WHERE email = ?";
        $stmt = $DB->prepare($sql);
        
        if ($stmt->execute($params)) {
            echo json_encode(["success" => "Mise à jour réussie"]);
        } else {
            echo json_encode(["error" => "Erreur lors de la mise à jour"]);
        }
    }
?>