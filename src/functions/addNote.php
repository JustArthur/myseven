<?php
// src/functions/addNote.php
require_once '../../database.php';

$data = json_decode(file_get_contents("php://input"), true);
$content = $data['notes_content'];

$pdo = new ConnexionDB();
$pdo = $pdo->openConnection();

if (isset($data['clients_id'])) {
    $stmt = $pdo->prepare("INSERT INTO notes (notes_clients_id, notes_text) VALUES (?, ?)");
    $stmt->execute([$data['clients_id'], $content]);

} elseif (isset($data['vehicules_id'])) {
    $stmt = $pdo->prepare("INSERT INTO notes (notes_vehicules_id, notes_text) VALUES (?, ?)");
    $stmt->execute([$data['vehicules_id'], $content]);

} else {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

echo json_encode(['success' => true]);
