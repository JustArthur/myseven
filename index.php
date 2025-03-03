<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require_once 'database.php';

    
    $DBB = new ConnexionDB();
    $DB = $DBB->DB();
    
    if (isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        session_id($_COOKIE['user_session']);
        
        session_start();
        
        $identifiant = $_COOKIE['user_session'];
        
        $stmt = $DB->prepare('SELECT * FROM users WHERE identifiantUser = ?');
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
    } else {
        header('Location: login.php');
        exit;
    }
    
    $tableauOnglets = [
        'Clients',
        'Véhicules',
        'Générer des PDF'
    ];

    $resClient = $DB->prepare('SELECT * FROM Clients ORDER BY nom ASC');
    $resClient->execute();
    $resClient = $resClient->fetchAll();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules ORDER BY immatriculation ASC');
    $resVehicule->execute();
    $resVehicule = $resVehicule->fetchAll();

    $DBB->closeConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        extract($_POST);
    
        $routes = [
            'generateMandatVente' => 'src/forms/saleMandateForm.php',
            'generateProcurationSignature' => 'src/pdf/generateSignatureAuthPDF.php',
            'generateBonReservation' => 'src/forms/reservationForm.php',
            'generateAccordBaissePrix' => 'src/pdf/generatePriceReductionPDF.php'
        ];

        if (empty($selectedCustomers) || empty($selectedVehicles)) {
            echo "<script>
                    alert('Veuillez sélectionner un client et un véhicule avant de continuer.');
                  </script>";
        } else {
            foreach ($routes as $key => $file) {
                if (isset($_POST[$key])) {
                    echo "
                        <form style='display:none' id='postForm' action='$file' target='_blank' method='POST'>
                            <input type='hidden' name='client' value='" . htmlspecialchars(string: $selectedCustomers) . "'>
                            <input type='hidden' name='immatCar' value='" . htmlspecialchars(string: $selectedVehicles) . "'>
                        </form>
                        <script>document.getElementById('postForm').submit();</script>
                    ";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/tables.css">

    <title>Myseven - Panel Administrateur</title>
</head>
<body>
    <form id="bigForm" method="POST">
        <div class="login">
            <?php if (!empty($_SESSION['user'])) { ?>
                <a href="logout.php" class="login-button deco">Se deconnecter</a>
            <?php } else {
                header('Location: login.php');
            } ?>
        </div>

        <div class="tableau">
            <div class="navbar">
                <?php foreach($tableauOnglets as $index => $onglet) { ?>
                    <a class="tab-button" data-tab="tab<?= $index + 1 ?>"><?= $onglet ?></a>
                <?php } ?>
            </div>

            <!-- Clients -->
            <div class="content" id="tab1">
                <h2><?= $tableauOnglets[0] ?></h2>
                <div class="input_client">
                    <input type="text" class="searchBar" id="searchBarCustomers" placeholder="Rechercher un client..." onkeyup="searchTable('customers', 'searchBarCustomers')">
                    <a href="https://natasha.myseven.fr:8443/form/41a58574-4ede-4b92-92d0-3c7242babbaf" target="_blank">Créer un client</a>
                </div>

                <div class="overflowTable">
                    <table class="table" id="customersTable">
                        <thead class="table-head" id="customersTableHead">
                            <tr class="table-row" id="customersTableHeadRow">
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Adresse-mail</th>
                                <th>Téléphone</th>
                                <th>Numéro et rue</th>
                                <th>Ville</th>
                                <th>Code postale</th>
                                <th>Numéro CNI</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="table-body" id="CustomersTableBody">
                            <!-- INSERT AVEC JS -->
                        </tbody>
                    </table>
                </div>

                <div class="pagination visible" id="paginationCustomers">
                    <a onclick="prevPage('Customers')">Page précédente</a>
                    <a onclick="nextPage('Customers')">Page suivante</a>
                    <span id="pageInfoCustomers"></span>
                </div>
            </div>

            <!-- Véhicules -->
            <div class="content" id="tab2">
                <h2><?= $tableauOnglets[1] ?></h2>
                <div class="input_vehicle">
                    <input type="text" class="searchBar" id="searchBarVehicles" placeholder="Rechercher un véhicule..." onkeyup="searchTable('vehicles', 'searchBarVehicles')">
                    <a href="https://natasha.myseven.fr:8443/form/738b1409-78e4-492e-9094-d5a77a40f48b" target="_blank">Créer un véhicule</a>
                </div>

                <div class="overflowTable">
                    <table class="table" id="vehicleTable">
                        <thead class="table-head" id="vehicleTableHead">
                            <tr class="table-row" id="vehicleTableHeadRow">
                                <th>Immatriculation</th>
                                <th>Marque</th>
                                <th>Modèle</th>
                                <th>Puissance</th>
                                <th>Type boite</th>
                                <th>Couleur</th>
                                <th>Kilomètrage</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="table-body" id="VehiclesTableBody">
                            <!-- INSERT AVEC JS -->
                        </tbody>
                    </table>
                </div>

                <div class="pagination visible" id="paginationVehicles">
                    <a onclick="prevPage('Vehicles')">Page précédente</a>
                    <a onclick="nextPage('Vehicles')">Page suivante</a>
                    <span id="pageInfoVehicles"></span>
                </div>
            </div>


            <div class="content" id="tab3">
                <h2><?= $tableauOnglets[2] ?></h2>
                <div class="btn_list">
                    <button type="submit" name="generateMandatVente" target="_blank" class="btn-generate action-link">Mandat de vente</button>
                    <button type="submit" name="generateProcurationSignature" target="_blank" class="btn-generate action-link">Procuration signature</button>
                    <button type="submit" name="generateBonReservation" target="_blank" class="btn-generate action-link">Bon de réservation</button>
                    <button type="submit" name="generateAccordBaissePrix" target="_blank" class="btn-generate action-link">Accord de baisse du prix net vendeur</button>
                </div>
            </div>
        </div>
    </form>
    

    <script>
        const rowsCustomers = [
            <?php
                $itemsCustomer = [];
                foreach ($resClient as $client) {
                    
                    $lastName = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $client['nom']));
                    $firstName = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $client['prenom']));
                    $email = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $client['email']));
                    $phone = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $client['telephone']));
                    $address = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $client['adresse']));
                    $ville = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $client['ville']));
                    $cp = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $client['cp']));
                    $numCNI = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $client['numero_cni']));

                    $itemsCustomer[] = 
                    "{
                        nom: \"$lastName\",
                        prenom: \"$firstName\",
                        email: \"$email\",
                        telephone: \"$phone\",
                        adresse: \"$address\",
                        ville: \"$ville\",
                        cp: \"$cp\",
                        numero_cni: \"$numCNI\"
                    }";
                }
                echo implode(separator: ",\n", array: $itemsCustomer);
            ?>
        ];

        const rowsVehicles = [
            <?php
                $itemsVehicule = [];
                foreach ($resVehicule as $vehicule) {
                   
                    $immatriculation = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $vehicule['immatriculation']));
                    $marque = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $vehicule['marque']));
                    $model = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $vehicule['model']));
                    $puissance = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $vehicule['puissance']));
                    $type_boite = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $vehicule['type_boite']));
                    $couleur = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $vehicule['couleur']));
                    $kilometrage = str_replace(search: ["\n", "\r"], replace: " ", subject: addslashes(string: $vehicule['kilometrage']));

                    $itemsVehicule[] = 
                    "{
                        immatriculation: \"$immatriculation\",
                        marque: \"$marque\",
                        model: \"$model\",
                        puissance: \"$puissance\",
                        type_boite: \"$type_boite\",
                        couleur: \"$couleur\",
                        kilometrage: \"$kilometrage\"
                    }";
                }
                echo implode(separator: ",\n", array: $itemsVehicule);
            ?>
        ];

        document.getElementById("bigForm").addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
            }
        });
    </script>
    
    <script type="text/javascript" src="assets/js/tableGenerator.js"></script>
    <script type="text/javascript" src="assets/js/navigation.js"></script>
</body>
</html>