<?php
require '../../database.php'; // Connexion PDO

$client_id = $_GET['client_id'] ?? null;
$vehicle_id = $_GET['vehicle_id'] ?? null;

$pdo = new ConnexionDB();
$pdo = $pdo->openConnection();

if ($client_id) {
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE notes_clients_id = :id ORDER BY notes_date DESC");
    $stmt->execute(['id' => $client_id]);
} elseif ($vehicle_id) {
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE notes_vehicules_id = :id ORDER BY notes_date DESC");
    $stmt->execute(['id' => $vehicle_id]);
} else {
    echo json_encode([]);
    exit;
}

$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($notes);