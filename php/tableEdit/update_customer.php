<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type");

    require_once('../../vendor/setasign/fpdi/src/autoload.php');
    require_once("../../connexionDB.php");

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();

    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!$data || !isset($data["email"])) {
        exit;
    }

    $fields = ["nom", "prenom", "telephone", "adresse", "ville", "cp", "numero_cni"];
    $updateFields = [];
    $params = [];

    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    $params[] = $data["email"];

    if (!empty($updateFields)) {
        $sql = "UPDATE Clients SET " . implode(", ", $updateFields) . " WHERE email = ?";
        $stmt = $DB->prepare($sql);
        
        if ($stmt->execute($params)) {
            // Success
        } else {
            // Error
            exit;
        }
    }
?>