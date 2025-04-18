<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    header('Content-Type: application/json');

    require_once '../../database.php';
    
    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    function searchTable($DB, $table, $term, $limit, $offset) {
        $clientType = null;
    
        if ($table == 'clientsVendeur') {
            $table = 'clients';
            $clientType = 'Vendeur';
        } else if ($table == 'clientsAcheteur') {
            $table = 'clients';
            $clientType = 'Acheteur';
        }
    
        // Tables autorisées
        $allowedTables = ['clients', 'vehicules'];
        if (!in_array($table, $allowedTables)) {
            throw new Exception("Table non autorisée.");
        }

        if ($table === 'clients') {
            $columnsToSelect = ['clients_id', 'clients_nom', 'clients_prenom', 'clients_email', 'clients_telephone', 'clients_rue', 'clients_ville', 'clients_cp', 'clients_numero_cni'];
            $orderBy = 'clients_nom ASC';
        } else {
            $columnsToSelect = ['vehicules_id', 'vehicules_immatriculation', 'vehicules_marque', 'vehicules_model', 'vehicules_annee', 'vehicules_puissance', 'vehicules_type_boite', 'vehicules_couleur', 'vehicules_kilometrage'];
            $orderBy = 'vehicules_immatriculation ASC';
        }
    
        $conditions = [];
        $params = [];
    
        foreach ($columnsToSelect as $column) {
            $conditions[] = "LOWER(CAST(`$column` AS CHAR)) LIKE ?";
            $params[] = '%' . strtolower($term) . '%';
        }
    
        $whereClause = implode(" OR ", $conditions);
        $columnsSQL = implode(', ', $columnsToSelect);
    
        if ($clientType !== null) {
            $sql = "SELECT $columnsSQL FROM `$table` WHERE `clients_type` = ? AND ($whereClause) ORDER BY $orderBy LIMIT ? OFFSET ?";
            array_unshift($params, $clientType);
            $params[] = $limit;
            $params[] = $offset;
        } else {
            $sql = "SELECT $columnsSQL FROM `$table` WHERE $whereClause ORDER BY $orderBy LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
    
        $stmt = $DB->prepare($sql);
        $stmt->execute($params);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    $table = $_GET['table'] ?? 'users';
    $term = $_GET['term'] ?? '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 50;
    $offset = ($page - 1) * $limit;

    try {
        $results = searchTable($DB, $table, $term, $limit, $offset);
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
?>
