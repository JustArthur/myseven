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
    $DB = $DBB->DB();

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data["oldUniqueValue"])) {
        echo json_encode(["error" => "Données invalides", "data" => $data]);
        exit;
    }

    $oldImmat = $data["oldUniqueValue"];
    $newImmat = $data["vehicules_immatriculation"];

    $fields = ["vehicules_immatriculation", "vehicules_marque", "vehicules_model", "vehicules_puissance", "vehicules_type_boite", "vehicules_couleur", "vehicules_kilometrage"];
    $updateFields = [];
    $params = [];

    if ($newImmat !== $oldImmat) {
        $checkEmail = $DB->prepare("SELECT COUNT(*) FROM vehicules WHERE vehicules_immatriculation = ?");
        $checkEmail->execute([$newImmat]);
        if ($checkEmail->fetchColumn() > 0) {
            echo json_encode(["error" => "Cet immatriculation est déjà utilisé."]);
            exit;
        }
    }

    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    $updateFields[] = "vehicules_immatriculation = ?";
    $params[] = $newImmat;
    
    $params[] = $oldImmat;

    if (!empty($updateFields)) {
        $sql = "UPDATE vehicules SET " . implode(", ", $updateFields) . " WHERE vehicules_immatriculation = ?";
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