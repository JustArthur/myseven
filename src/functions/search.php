<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    header('Content-Type: application/json');

    require_once '../../database.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    function buildSearchConditions($columns, $term) {
        $conditions = [];
        $params = [];

        foreach ($columns as $column) {
            $conditions[] = "LOWER(CAST(`$column` AS CHAR)) LIKE ?";
            $params[] = '%' . strtolower($term) . '%';
        }

        return ['where' => implode(" OR ", $conditions), 'params' => $params];
    }

    function countResults($DB, $table, $term, $clientType, $columnsToSearch) {
        $search = buildSearchConditions($columnsToSearch, $term);

        if ($clientType !== null) {
            $sql = "SELECT COUNT(*) FROM `$table` WHERE `clients_type` = ? AND ({$search['where']})";
            array_unshift($search['params'], $clientType);
        } else {
            $sql = "SELECT COUNT(*) FROM `$table` WHERE {$search['where']}";
        }

        $stmt = $DB->prepare($sql);
        $stmt->execute($search['params']);
        return $stmt->fetchColumn();
    }

    function searchTable($DB, $table, $term, $limit, $offset, $clientType) {
        if ($table === 'clients') {
            $columnsToSelect = ['clients_id', 'clients_nom', 'clients_prenom', 'clients_email', 'clients_telephone', 'clients_rue', 'clients_ville', 'clients_cp', 'clients_numero_cni'];
            $orderBy = 'clients_nom ASC';
        } else {
            $columnsToSelect = ['vehicules_id', 'vehicules_immatriculation', 'vehicules_marque', 'vehicules_model', 'vehicules_annee', 'vehicules_puissance', 'vehicules_type_boite', 'vehicules_couleur', 'vehicules_kilometrage'];
            $orderBy = 'vehicules_immatriculation ASC';
        }

        $search = buildSearchConditions($columnsToSelect, $term);
        $columnsSQL = implode(', ', $columnsToSelect);

        if ($clientType !== null) {
            $sql = "SELECT $columnsSQL FROM `$table` WHERE `clients_type` = ? AND ({$search['where']}) ORDER BY $orderBy LIMIT ? OFFSET ?";
            array_unshift($search['params'], $clientType);
        } else {
            $sql = "SELECT $columnsSQL FROM `$table` WHERE {$search['where']} ORDER BY $orderBy LIMIT ? OFFSET ?";
        }

        array_push($search['params'], $limit, $offset);

        $stmt = $DB->prepare($sql);
        $stmt->execute($search['params']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $table = $_GET['table'] ?? 'users';
    $term = $_GET['term'] ?? '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 25;
    $offset = ($page - 1) * $limit;

    $clientType = null;
    if ($table === 'clientsVendeur') {
        $table = 'clients';
        $clientType = 'Vendeur';
    } elseif ($table === 'clientsAcheteur') {
        $table = 'clients';
        $clientType = 'Acheteur';
    }

    try {
        $columnsToSearch = $table === 'clients'
            ? ['clients_id', 'clients_nom', 'clients_prenom', 'clients_email', 'clients_telephone', 'clients_rue', 'clients_ville', 'clients_cp', 'clients_numero_cni']
            : ['vehicules_id', 'vehicules_immatriculation', 'vehicules_marque', 'vehicules_model', 'vehicules_annee', 'vehicules_puissance', 'vehicules_type_boite', 'vehicules_couleur', 'vehicules_kilometrage'];

        $totalResults = countResults($DB, $table, $term, $clientType, $columnsToSearch);
        $totalPages = ceil($totalResults / $limit);
        $results = searchTable($DB, $table, $term, $limit, $offset, $clientType);

        echo json_encode([
            'results' => $results,
            'totalResults' => $totalResults,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
?>