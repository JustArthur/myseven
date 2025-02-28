<?php
    ini_set(option: 'display_errors', value: '1');
    ini_set(option: 'display_startup_errors', value: '1');
    error_reporting(error_level: E_ALL);

    require_once 'connexionDB.php';

    $tableauOnglets = [
        'Clients',
        'Véhicules',
        'Générer des PDF'
    ];

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();

    if (isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        session_id(id: $_COOKIE['user_session']);

        session_start();
    
        $identifiant = $_COOKIE['user_session'];

        $stmt = $DB->prepare(query: 'SELECT * FROM users WHERE identifiantUser = ?');
        $stmt->execute(params: [$identifiant]);
        $user = $stmt->fetch();
    
        if ($user) {
            $_SESSION['user'] = array(
                'id' => htmlspecialchars(string: $user['idUser'], flags: ENT_QUOTES),
                'identifiant' => htmlspecialchars(string: $user['identifiantUser'], flags: ENT_QUOTES)
            );
        } else {
            session_destroy();
        }
    } else {
        header(header: 'Location: ./php/login.php');
    }

    $resClient = $DB->prepare('SELECT * FROM Clients ORDER BY nom ASC');
    $resClient->execute();
    $resClient = $resClient->fetchAll();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules ORDER BY immatriculation ASC');
    $resVehicule->execute();
    $resVehicule = $resVehicule->fetchAll();

    $DBB->closeConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        extract(array: $_POST);
    
        $routes = [
            'generateMandatVente' => './php/forms/formMandatVente.php',
            'generateProcurationSignature' => './php/generatePDF/generateProcurationSignature.php',
            'generateBonReservation' => './php/forms/formBonReservation.php',
            'generateAccordBaissePrix' => './php/generatePDF/generateAccordBaissePrix.php'
        ];

        if (empty($selectedCustomer) || empty($selectedVehicule)) {
            echo "<script>
                    alert('Veuillez sélectionner un client et un véhicule avant de continuer.');
                  </script>";
        } else {
            foreach ($routes as $key => $file) {
                if (isset($_POST[$key])) {
                    echo "
                        <form style='display:none' id='postForm' action='$file' target='_blank' method='POST'>
                            <input type='hidden' name='client' value='" . htmlspecialchars(string: $selectedCustomer) . "'>
                            <input type='hidden' name='immatCar' value='" . htmlspecialchars(string: $selectedVehicule) . "'>
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

    <link rel="stylesheet" href="./style/style.css">

    <title>Myseven - Panel Administrateur</title>
</head>
<body>
    <form id="bigForm" method="POST">
        <div class="login">
            <?php if (!empty($_SESSION['user'])) { ?>
                <a href="./php/logout.php" class="login-button deco">Se deconnecter</a>
            <?php } else {
                header(header: 'Location: ./php/login.php');
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
                <input type="text" class="searchBar" id="searchBarCustomer" placeholder="Rechercher un client..." onkeyup="searchCustomers()">

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
                        <tbody class="table-body" id="customersTableBody">
                            <!-- INSERT AVEC JS -->
                        </tbody>
                    </table>
                </div>

                <div class="pagination visible" id="paginationCustomer">
                    <a onclick="prevPage()">Page précédente</a>
                    <a onclick="nextPage()">Page suivante</a>
                    <span id="pageInfoCustomer"></span>
                </div>
            </div>

            <!-- Véhicules -->
            <div class="content" id="tab2">
                <h2><?= $tableauOnglets[1] ?></h2>
                    <input type="text" class="searchBar" id="searchBarVehicule" placeholder="Rechercher un véhicule..." onkeyup="searchVehicule()">

                    <div class="overflowTable">
                        <table class="table" id="vehiculeTable">
                            <thead class="table-head" id="vehiculeTableHead">
                                <tr class="table-row" id="vehiculeTableHeadRow">
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
                            <tbody class="table-body" id="vehiculeTableBody">
                                <!-- INSERT AVEC JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination visible" id="paginationVehicule">
                        <a onclick="prevPageVehicule()">Page précédente</a>
                        <a onclick="nextPageVehicule()">Page suivante</a>
                        <span id="pageInfoVehicule"></span>
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
                    $lastName = addslashes(string: $client['nom']);
                    $firstName = addslashes(string: $client['prenom']);
                    $email = addslashes(string: $client['email']);
                    $phone = addslashes(string: $client['telephone']);
                    $address = addslashes(string: $client['adresse']);
                    $ville = addslashes(string: $client['ville']);
                    $cp = addslashes(string: $client['cp']);
                    $numCNI = addslashes(string: $client['numero_cni']);
                    
                    $lastName = str_replace(search: ["\n", "\r"], replace: " ", subject: $lastName);
                    $firstName = str_replace(search: ["\n", "\r"], replace: " ", subject: $firstName);
                    $email = str_replace(search: ["\n", "\r"], replace: " ", subject: $email);
                    $phone = str_replace(search: ["\n", "\r"], replace: " ", subject: $phone);
                    $address = str_replace(search: ["\n", "\r"], replace: " ", subject: $address);
                    $ville = str_replace(search: ["\n", "\r"], replace: " ", subject: $ville);
                    $cp = str_replace(search: ["\n", "\r"], replace: " ", subject: $cp);
                    $numCNI = str_replace(search: ["\n", "\r"], replace: " ", subject: $numCNI);

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

        const rowsVehicules = [
            <?php
                $itemsVehicule = [];
                foreach ($resVehicule as $vehicule) {
                    $immatriculation = addslashes(string: $vehicule['immatriculation']);
                    $marque = addslashes(string: $vehicule['marque']);
                    $model = addslashes(string: $vehicule['model']);
                    $puissance = addslashes(string: $vehicule['puissance']);
                    $type_boite = addslashes(string: $vehicule['type_boite']);
                    $couleur = addslashes(string: $vehicule['couleur']);
                    $kilometrage = addslashes(string: $vehicule['kilometrage']);
                    
                    $immatriculation = str_replace(search: ["\n", "\r"], replace: " ", subject: $immatriculation);
                    $marque = str_replace(search: ["\n", "\r"], replace: " ", subject: $marque);
                    $model = str_replace(search: ["\n", "\r"], replace: " ", subject: $model);
                    $puissance = str_replace(search: ["\n", "\r"], replace: " ", subject: $puissance);
                    $type_boite = str_replace(search: ["\n", "\r"], replace: " ", subject: $type_boite);
                    $couleur = str_replace(search: ["\n", "\r"], replace: " ", subject: $couleur);
                    $kilometrage = str_replace(search: ["\n", "\r"], replace: " ", subject: $kilometrage);

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
    <script type="text/javascript" src="js/customersTable.js"></script>
    <script type="text/javascript" src="js/vehiculeTable.js"></script>

    <script type="text/javascript" src="js/navigation.js"></script>
</body>
</html>