<?php
    require_once '../../database.php';
    header('Content-Type: application/json');

    $pdo = new ConnexionDB();
    $pdo = $pdo->openConnection();

    $table = $_GET['table'] ?? '';
    $id = intval($_GET['id'] ?? 0);

    $allowedTables = ['clients', 'vehicules'];
    if (!in_array($table, $allowedTables)) {
        http_response_code(400);
        echo json_encode(['error' => 'Table invalide']);
        exit;
    }

    $idColumn = $table === 'clients' ? 'clients_id' : 'vehicules_id';

    $stmt = $pdo->prepare("SELECT * FROM $table WHERE $idColumn = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Encodage base64 des BLOB si présent
    if ($data) {
        if ($table === 'clients' && isset($data['clients_copie_cni'])) {
            $mimeType = finfo_buffer(finfo_open(), $data['clients_copie_cni'], FILEINFO_MIME_TYPE);
            $base64 = base64_encode($data['clients_copie_cni']);
            $data['clients_copie_cni'] = "data:$mimeType;base64,$base64";
        }

        if ($table === 'vehicules' && isset($data['vehicules_carte_grise'])) {
            $mimeType = finfo_buffer(finfo_open(), $data['vehicules_carte_grise'], FILEINFO_MIME_TYPE);
            $base64 = base64_encode($data['vehicules_carte_grise']);
            $data['vehicules_carte_grise'] = "data:$mimeType;base64,$base64";
        }
    }

    echo json_encode($data);
?>