<?php
    ini_set(option: 'display_errors', value: '1');
    ini_set(option: 'display_startup_errors', value: '1');
    error_reporting(error_level: E_ALL);

    header("Content-Type: application/json");

    require_once '../../vendor/setasign/fpdi/src/autoload.php';
    require_once '../../connexionDB.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();

    $data = json_decode(json: file_get_contents(filename: "php://input"), associative: true);

    if (!$data || !isset($data["oldEmail"])) {
        echo json_encode(value: ["error" => "Données invalides", "data" => $data]);
        exit;
    }

    $oldEmail = $data["oldEmail"];
    $newEmail = $data["email"];

    $fields = ["nom", "prenom", "email", "telephone", "adresse", "ville", "cp", "numero_cni"];
    $updateFields = [];
    $params = [];

    if(filter_var(value: $newEmail, filter: FILTER_VALIDATE_EMAIL) === false) {
        echo json_encode(value: ["error" => "Email invalide"]);
        exit;
    }

    if ($newEmail !== $oldEmail) {
        $checkEmail = $DB->prepare(query: "SELECT COUNT(*) FROM Clients WHERE email = ?");
        $checkEmail->execute(params: [$newEmail]);
        
        if ($checkEmail->fetchColumn() > 0) {
            echo json_encode(value: ["error" => "Cet email est déjà utilisé."]);
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
        $sql = "UPDATE Clients SET " . implode(separator: ", ", array: $updateFields) . " WHERE email = ?";
        $stmt = $DB->prepare(query: $sql);
        
        if ($stmt->execute(params: $params)) {
            echo json_encode(value: ["success" => "Mise à jour réussie"]);
            $DBB->closeConnection();
            exit;

        } else {
            echo json_encode(value: ["error" => "Erreur lors de la mise à jour"]);
            $DBB->closeConnection();
            exit;
        }
    }
?>