<?php 
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: application/json; charset=utf-8');

    require_once '../../database.php';

    $pdo = new ConnexionDB();
    $pdo = $pdo->openConnection();

    // Assure-toi que l'émulation des préparations est activée
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

    $table = $_GET['table'] ?? '';
    $page = intval($_GET['page'] ?? 1);
    $term = $_GET['term'] ?? '';
    $agence = $_GET['agence'] ?? 'All';
    $clientType = $_GET['client_type'] ?? '';

    $rowsPerPage = 25;
    $offset = ($page - 1) * $rowsPerPage;

    $where = "1=1";
    $params = [];

    $allowedTables = ['clients', 'vehicules'];
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['error' => 'Table non autorisée']);
        exit;
    }

    if (!empty($term)) {
        $where .= " AND (clients_nom LIKE :term OR clients_prenom LIKE :term OR clients_email LIKE :term)";
        $params[':term'] = "%$term%";
    }

    if ($table === 'clients' && $clientType) {
        $where .= " AND clients_type = :type";
        $params[':type'] = $clientType;
    }

    if ($agence !== "All") {
        if ($table === 'clients') {
            $where .= " AND clients_agence_id = :agence";
            $params[':agence'] = $agence;
        } elseif ($table === 'vehicules') {
            $where .= " AND vehicules_agence_id = :agence";
            $params[':agence'] = $agence;
        }
    }

    // Build ORDER BY
    $order = "ORDER BY ";
    if ($table === 'clients') {
        $order .= "clients_nom ASC";
    } elseif ($table === 'vehicules') {
        $order .= "vehicules_immatriculation ASC";
    }

    // Get total
    $sqlCount = "SELECT COUNT(*) FROM $table WHERE $where";
    $stmt = $pdo->prepare($sqlCount);
    $stmt->execute($params);
    $totalRows = $stmt->fetchColumn();
    $totalPages = ceil($totalRows / $rowsPerPage);

    // Prepare the data query
    // Inject limit and offset directly into the SQL string
    $sql = "SELECT * FROM $table WHERE $where $order LIMIT $rowsPerPage OFFSET $offset";
    $stmt = $pdo->prepare($sql);

    // Bind other parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    
    // Fetch all rows, not just one
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert BLOBs to base64
    foreach ($rows as &$row) {
        // Si c'est un BLOB (par exemple 'clients_copie_cni' ou 'vehicules_carte_grise')
        if (isset($row['clients_copie_cni'])) {
            $row['clients_copie_cni'] = base64_encode($row['clients_copie_cni']);
        }

        if (isset($row['vehicules_carte_grise'])) {
            $row['vehicules_carte_grise'] = base64_encode($row['vehicules_carte_grise']);
        }
    }

    // Prepare the response with the rows and total pages
    $jsonResponse = json_encode([
        'rows' => $rows,
        'totalPages' => $totalPages,
    ]);
    
    // Check for JSON encoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'Erreur lors de l\'encodage JSON: ' . json_last_error_msg()]);
        exit;
    }
    
    echo $jsonResponse;
    exit;  
?>