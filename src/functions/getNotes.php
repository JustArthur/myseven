<?php
    require_once '../../database.php'; // Ta connexion PDO ici

    header('Content-Type: application/json');

    $pdo = new ConnexionDB();
    $pdo = $pdo->openConnection();

    $clientId = isset($_GET['clients_id']) ? intval($_GET['clients_id']) : null;
    $vehicleId = isset($_GET['vehicle_id']) ? intval($_GET['vehicle_id']) : null;

    try {
        if ($clientId) {
            $stmt = $pdo->prepare("SELECT * FROM notes WHERE notes_clients_id = ? ORDER BY notes_date DESC");
            $stmt->execute([$clientId]);
        } elseif ($vehicleId) {
            $stmt = $pdo->prepare("SELECT * FROM notes WHERE notes_vehicle_id = ? ORDER BY notes_date DESC");
            $stmt->execute([$vehicleId]);
        } else {
            echo json_encode(["error" => "Aucun ID fourni"]);
            exit;
        }

        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($notes);

    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
?>