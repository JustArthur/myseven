<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    require_once( "connexionDB.php");


    $DBB = new ConnexionDB();
    $DB = $DBB->DB();

    if (isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        session_id($_COOKIE['user_session']);

        session_start();
    
        $identifiant = $_COOKIE['user_session'];
        
        $stmt = $DB->prepare('SELECT * FROM testtablelogin WHERE identifiantUser = ?');
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch();
    
        if ($user) {
            $_SESSION['user'] = array(
                'id' => htmlspecialchars($user['idUser'], ENT_QUOTES),
                'identifiant' => htmlspecialchars($user['identifiantUser'], ENT_QUOTES)
            );
        } else {
            session_destroy();
        }
    }

    $resClient = $DB->prepare('SELECT * FROM Clients ORDER BY nom ASC');
    $resClient->execute();
    $resClient = $resClient->fetchAll();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules ORDER BY immatriculation ASC');
    $resVehicule->execute();
    $resVehicule = $resVehicule->fetchAll();

    $DBClose = $DBB->closeConnection();

    if (!empty($_SESSION['user'])) {
        var_dump($_SESSION['user']);
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="./style/style.css">

    <title>Document</title>
</head>
<body>

    <div class="login">
        <?php if (!empty($_SESSION['user'])) { ?>
            <a href="./php/logout.php" class="login-button deco">Se deconnecter</a>
        <?php } else {?>
            <a href="./php/login.php" class="login-button">Se connecter</a>
        <?php } ?>
    </div>

    <div class="tableau">
        <div class="navbar">
            <button class="tab-button active" data-tab="tab1">Clients</button>
            <button class="tab-button" data-tab="tab2">Véhicules</button>
            <button class="tab-button" data-tab="tab3">Onglet 3</button>
            <button class="tab-button" data-tab="tab4">Onglet 4</button>
        </div>

        <!-- Clients -->
        <div class="content active" id="tab1">
            <h2>Clients</h2>
            <input type="text" class="searchBar" id="searchBarCustomer" placeholder="Rechercher un client..." onkeyup="searchCustomers()">

            <table class="table" id="customersTable">
                <thead class="table-head" id="customersTableHead">
                    <tr class="table-row" id="customersTableHeadRow">
                        <th>Nom de famille</th>
                        <th>Prénom</th>
                        <th>Adresse-mail</th>
                        <th>Numéro CNI</th>
                    </tr>
                </thead>
                <tbody class="table-body" id="customersTableBody">
                    <tr></tr>
                </tbody>
            </table>

            <div class="pagination visible" id="paginationCustomer">
                <button onclick="prevPage()">Page précédente</button>
                <button onclick="nextPage()">Page suivante</button>
                <span id="pageInfoCustomer"></span>
            </div>
        </div>

        <!-- Véhicules -->
        <div class="content" id="tab2">
            <h2>Véhicules</h2>
                <input type="text" class="searchBar" id="searchBarVehicule" placeholder="Rechercher un véhicule..." onkeyup="searchVehicule()">

                <table class="table" id="vehiculeTable">
                    <thead class="table-head" id="vehiculeTableHead">
                        <tr class="table-row" id="vehiculeTableHeadRow">
                            <th>Immatriculation</th>
                            <th>Marque</th>
                            <th>Modèle</th>
                            <th>Kilomètrage</th>
                        </tr>
                    </thead>
                    <tbody class="table-body" id="vehiculeTableBody">
                        <tr></tr>
                    </tbody>
                </table>

                <div class="pagination visible" id="paginationVehicule">
                    <button onclick="prevPageVehicule()">Page précédente</button>
                    <button onclick="nextPageVehicule()">Page suivante</button>
                    <span id="pageInfoVehicule"></span>
                </div>
        </div>

        <div class="content" id="tab3">
            <h2>Onglet 3</h2>
            <p>test desc</p>
        </div>

        <div class="content" id="tab4">
            <h2>Onglet 4</h2>
            <p>test desc</p>
        </div>
    </div>
    

    <script>
        const rowsCustomers = [
            <?php
                $itemsCustomer = [];
                foreach ($resClient as $client) {
                    $itemsCustomer[] = 
                    "{
                        lastName: \"" . addslashes($client['nom']) . "\",
                        firstName: \"" . addslashes($client['prenom']) . "\",
                        email: \"" . addslashes($client['email']) . "\",
                        numCNI: \"" . addslashes($client['numero_cni']) . "\"
                    }";
                }
                echo implode(",\n", $itemsCustomer);
            ?>
        ];

        const rowsVehicules = [
            <?php
                $itemsVehicule = [];
                foreach ($resVehicule as $vehicule) {
                    $itemsVehicule[] = 
                    "{
                        immatriculation: \"" . addslashes($vehicule['immatriculation']) . "\",
                        marque: \"" . addslashes($vehicule['marque']) . "\",
                        model: \"" . addslashes($vehicule['model']) . "\",
                        kilometrage: \"" . addslashes($vehicule['kilometrage']) . "\"
                    }";
                }
                echo implode(",\n", $itemsVehicule);
            ?>
        ];
    </script>
    <script type="text/javascript" src="js/customersTable.js"></script>
    <script type="text/javascript" src="js/vehiculeTable.js"></script>

    <script type="text/javascript" src="js/navigation.js"></script>
</body>
</html>