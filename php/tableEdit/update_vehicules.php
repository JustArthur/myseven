<?php
    ini_set(option: 'display_errors', value: '1');
    ini_set(option: 'display_startup_errors', value: '1');
    error_reporting(error_level: E_ALL);

    header(header: "Content-Type: application/json");

    require_once '../../vendor/setasign/fpdi/src/autoload.php';
    require_once '../../connexionDB.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();

    $data = json_decode(json: file_get_contents(filename: "php://input"), associative: true);

    if (!$data || !isset($data["oldImmat"])) {
        echo json_encode(value: ["error" => "Données invalides", "data" => $data]);
        exit;
    }

    $oldImmat = $data["oldImmat"];
    $newImmat = $data["immatriculation"];

    $fields = ["immatriculation", "marque", "model", "puissance", "type_boite", "couleur", "kilometrage"];
    $updateFields = [];
    $params = [];

    if ($newImmat !== $oldImmat) {
        $checkEmail = $DB->prepare(query: "SELECT COUNT(*) FROM vehicules WHERE immatriculation = ?");
        $checkEmail->execute(params: [$newImmat]);
        if ($checkEmail->fetchColumn() > 0) {
            echo json_encode(value: ["error" => "Cet immatriculation est déjà utilisé."]);
            exit;
        }
    }

    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    $updateFields[] = "immatriculation = ?";
    $params[] = $newImmat;
    
    $params[] = $oldImmat;

    if (!empty($updateFields)) {
        $sql = "UPDATE vehicules SET " . implode(separator: ", ", array: $updateFields) . " WHERE immatriculation = ?";
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