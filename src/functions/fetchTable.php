<?php 
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    header('Content-Type: application/json; charset=utf-8');

    require_once '../../database.php';

    $pdo = new ConnexionDB();
    $pdo = $pdo->openConnection();

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

    $order = "ORDER BY ";
    if ($table === 'clients') {
        $order .= "clients_nom ASC";
    } elseif ($table === 'vehicules') {
        $order .= "vehicules_immatriculation ASC";
    }

    $sqlCount = "SELECT COUNT(*) FROM $table WHERE $where";
    $stmt = $pdo->prepare($sqlCount);
    $stmt->execute($params);
    $totalRows = $stmt->fetchColumn();
    $totalPages = ceil($totalRows / $rowsPerPage);

    $selectFields = "*";
    if ($table === 'clients') {
        $selectFields = "clients_nom, clients_prenom, clients_email, clients_telephone, clients_rue, clients_ville, clients_cp, clients_numero_cni, clients_id, clients_agence_id";
    } elseif ($table === 'vehicules') {
        $selectFields = "vehicules_immatriculation, vehicules_marque, vehicules_model, vehicules_annee, vehicules_puissance, vehicules_type_boite, vehicules_couleur, vehicules_kilometrage, vehicules_id, vehicules_agence_id";
    }

    $sql = "SELECT $selectFields FROM $table WHERE $where $order LIMIT $rowsPerPage OFFSET $offset";
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $jsonResponse = json_encode([
        'rows' => $rows,
        'totalPages' => $totalPages,
    ]);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'Erreur lors de l\'encodage JSON: ' . json_last_error_msg()]);
        exit;
    }
    
    echo $jsonResponse;
    exit;  
?>