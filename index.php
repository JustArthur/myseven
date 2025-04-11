<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require_once 'database.php';
    require_once 'src/functions/selectSQL.php';
    require_once 'src/functions/generateTable.php';
    
    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    session_start();

    if(!isset($_SESSION['user']) || empty($_COOKIE['user_session']) || empty($_SESSION['user']['agence_id'])) {
        header('Location: login.php');
        exit();
    }
    
    $tableauOnglets = [
        'Clients vendeur',
        'Véhicules',
        'Clients acheteur',
        'PDF'
    ];

    if ($_SERVER['HTTP_HOST'] === '127.0.0.1' || $_SERVER['HTTP_HOST'] === 'myseven') {
        var_dump($_SESSION);
    }

    $resAgence = selectAllAgence($DB);
    $resAgence = $resAgence->fetchAll();
    
    $resClientVendeur = selectAllClientVendeur($DB);
    $resClientVendeur = $resClientVendeur->fetchAll();

    $resClientAcheteur = selectAllClientAcheteur($DB);
    $resClientAcheteur = $resClientAcheteur->fetchAll();

    $resVehicule = selectAllVehicle($DB);
    $resVehicule = $resVehicule->fetchAll();

    if($_SESSION['user']['role'] == 1) { $tableauOnglets[] = 'Excel'; }
    // else {
    //     $resClientVendeur = selectAllClientVendeurWhereAgence($_SESSION['user']['agence_id'], $DB);
    //     $resClientVendeur = $resClientVendeur->fetchAll();

    //     $resClientAcheteur = selectAllClientVendeurWhereAgence($_SESSION['user']['agence_id'], $DB);
    //     $resClientAcheteur = $resClientAcheteur->fetchAll();

    //     $resVehicule = selectAllVehicleWhereAgence($_SESSION['user']['agence_id'], $DB);
    //     $resVehicule = $resVehicule->fetchAll();
    // }

    $DBB->closeConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        extract($_POST);
    
        $routes = [
            'generateMandatVente' => 'src/forms/saleMandateForm.php',
            'generateProcurationSignature' => 'src/pdf/generateSignatureAuthPDF.php',
            'generateBonReservation' => 'src/forms/reservationForm.php',
            'generateAccordBaissePrix' => 'src/forms/priceReductionForm.php',
            'generateContractEngagement' => 'src/forms/contractEngagementForm.php',
            'generateInformationSell' => 'src/pdf/generateInformationSell.php'
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
                            <input type='hidden' name='clientEmail' value='" . htmlspecialchars($selectedCustomers) . "'>
                            <input type='hidden' name='immatCar' value='" . htmlspecialchars($selectedVehicles) . "'>
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=close" />

    <link rel="stylesheet" href="assets/css/tables.css">
    <link rel="stylesheet" href="assets/css/cardProfile.css">

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

            <!-- Clients Vendeur -->
            <div class="content" id="tab1">
                <h2><?= $tableauOnglets[0] ?></h2>
                <div class="input_client">
                    <input type="text" class="searchBar" id="searchBarCustomersSell" placeholder="Rechercher un client vendeur..." onkeyup="searchTable('CustomersSell', 'searchBarCustomersSell')">
                    <select class="inputSelect" name="selectAgenceSell" id="selectAgenceSell" onchange="window.selectAgence('CustomersSell', 'selectAgenceSell')">
                        <optgroup label="Choisir l'agence pour trier les clients vendeurs">
                            <option value="All">Toute les agences</option>
                            <?php foreach($resAgence as $agence) { ?>
                                <option value="<?= $agence['agence_id'] ?>"><?= $agence['agence_nom'] ?></option>
                            <?php } ?>
                        </optgroup>
                    </select>
                    <a href="./src/forms/customerForm.php?customerType=2">Créer un client vendeur</a>
                </div>

                <div class="overflowTable">
                    <table class="table" id="CustomersSellTable">
                        <thead class="table-head" id="CustomersSellTableHead">
                            <tr class="table-row" id="CustomersSellTableHeadRow">
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Adresse-mail</th>
                                <th>Téléphone</th>
                                <th>Numéro et rue</th>
                                <th>Ville</th>
                                <th>Code postal</th>
                                <th>Numéro CNI</th>
                            </tr>
                        </thead>
                        <tbody class="table-body" id="CustomersSellTableBody">
                            <!-- INSERT AVEC JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Véhicules -->
            <div class="content" id="tab2">
                <h2><?= $tableauOnglets[1] ?></h2>
                <div class="input_vehicle">
                    <input type="text" class="searchBar" id="searchBarVehicles" placeholder="Rechercher un véhicule..." onkeyup="searchTable('Vehicles', 'searchBarVehicles')">
                    <select class="inputSelect" name="selectAgenceVehicles" id="selectAgenceVehicles" onchange="window.selectAgence('Vehicles', 'selectAgenceVehicles')">
                        <optgroup label="Choisir l'agence pour trier les véhicules">
                            <option value="All">Toute les agences</option>
                            <?php foreach($resAgence as $agence) { ?>
                                <option value="<?= $agence['agence_id'] ?>"><?= $agence['agence_nom'] ?></option>
                            <?php } ?>
                        </optgroup>
                    </select>
                    <a href="./src/forms/vehicleForm.php">Créer un véhicule</a>
                </div>

                <div class="overflowTable">
                    <table class="table" id="VehiclesTable">
                        <thead class="table-head" id="VehiclesTableHead">
                            <tr class="table-row" id="VehiclesTableHeadRow">
                                <th>Immatriculation</th>
                                <th>Marque</th>
                                <th>Modèle</th>
                                <th>Année</th>
                                <th>Puissance</th>
                                <th>Type boite</th>
                                <th>Couleur</th>
                                <th>Kilomètrage</th>
                            </tr>
                        </thead>
                        <tbody class="table-body" id="VehiclesTableBody">
                            <!-- INSERT AVEC JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Clients Acheteur -->
            <div class="content" id="tab3">
                <h2><?= $tableauOnglets[2] ?></h2>
                <div class="input_client">
                    <input type="text" class="searchBar" id="searchBarCustomersBuy" placeholder="Rechercher un client acheteur..." onkeyup="searchTable('CustomersBuy', 'searchBarCustomersBuy')">
                    <select class="inputSelect" name="selectAgenceBuy" id="selectAgenceBuy" onchange="window.selectAgence('CustomersBuy', 'selectAgenceBuy')">
                        <optgroup label="Choisir l'agence pour trier les clients achteurs">
                            <option value="All">Toute les agences</option>
                            <?php foreach($resAgence as $agence) { ?>
                                <option value="<?= $agence['agence_id'] ?>"><?= $agence['agence_nom'] ?></option>
                            <?php } ?>
                        </optgroup>
                    </select>
                    <a href="./src/forms/customerForm.php?customerType=1">Créer un client acheteur</a>
                </div>

                <div class="overflowTable">
                    <table class="table" id="CustomersBuyTable">
                        <thead class="table-head" id="CustomersBuyTableHead">
                            <tr class="table-row" id="CustomersBuyTableHeadRow">
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Adresse-mail</th>
                                <th>Téléphone</th>
                                <th>Numéro et rue</th>
                                <th>Ville</th>
                                <th>Code postal</th>
                                <th>Numéro CNI</th>
                            </tr>
                        </thead>
                        <tbody class="table-body" id="CustomersBuyTableBody">
                            <!-- INSERT AVEC JS -->
                        </tbody>
                    </table>
                </div>
            </div>


            <div class="content" id="tab4">
                <h2><?= $tableauOnglets[3] ?></h2>
                <div class="btn_list">
                    <button type="submit" target="_blank" name="generateMandatVente" class="btn-generate action-link">Mandat de vente</button>
                    <button type="submit" target="_blank" name="generateContractEngagement" class="btn-generate action-link">Mandat d'engagement</button>
                    <button type="submit" target="_blank" name="generateProcurationSignature" class="btn-generate action-link">Procuration signature</button>
                    <button type="submit" target="_blank" name="generateBonReservation" class="btn-generate action-link">Bon de réservation</button>
                    <button type="submit" target="_blank" name="generateAccordBaissePrix" class="btn-generate action-link">Accord de baisse du prix net vendeur</button>
                    <button type="submit" target="_blank" name="generateInformationSell" class="btn-generate action-link">Information relative à la vente</button>
                </div>
            </div>

            <div class="content" id="tab5">
                <h2><?= $tableauOnglets[4] ?></h2>
                <div class="btn_list excel">
                    <a href="src/excel/dumpExcel.php" target="_blank" class="btn-generate action-link">Générer un Excel</a>
                </div>
            </div>
        </div>
    </form>

    <!-- Overlay (fond sombre) -->
    <div id="overlay" class="cardItem-overlay hidden"></div>

    <!-- Popup -->
    <div id="cardItem" class="cardItem hidden">
        <div id="cardItem_content" class=cardItem_content>
            <!-- Le contenu de la popup sera inséré dynamiquement ici -->
        </div>
    </div>

    
    <script>
        // Fonction pour générer les lignes du tableau à partir des données
        const rowsCustomersSell = [<?= generateRows($resClientVendeur, $customerFields) ?>];
        const rowsCustomersBuy = [<?= generateRows($resClientAcheteur, $customerFields) ?>];
        const rowsVehicles = [<?= generateRows($resVehicule, $vehicleFields) ?>];

        // Désactiver le entrée du grand form
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