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

    if (empty($_SESSION['user']) && !empty($_COOKIE['user_session'])) {
        header('Location: login.php');
        exit();
    }
    
    $tableauOnglets = [
        'Clients acheteur',
        'Clients vendeur',
        'Véhicules',
        'Générer des PDF'
    ];

    $resAgence = selectAllAgence($DB);
    $resAgence = $resAgence->fetchAll();

    if($_SESSION['user']['role'] == 1) {
        $resClientVendeur = selectAllClientVendeur($DB);
        $resClientVendeur = $resClientVendeur->fetchAll();

        $resClientAcheteur = selectAllClientAcheteur($DB);
        $resClientAcheteur = $resClientAcheteur->fetchAll();

        $resVehicule = selectAllVehicle($DB);
        $resVehicule = $resVehicule->fetchAll();
    } else {
        $resClientVendeur = selectAllClientVendeurWhereAgence($_SESSION['user']['agence_id'], $DB);
        $resClientVendeur = $resClientVendeur->fetchAll();

        $resClientAcheteur = selectAllClientVendeurWhereAgence($_SESSION['user']['agence_id'], $DB);
        $resClientAcheteur = $resClientAcheteur->fetchAll();

        $resVehicule = selectAllVehicleWhereAgence($_SESSION['user']['agence_id'], $DB);
        $resVehicule = $resVehicule->fetchAll();
    }

    $DBB->closeConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        extract($_POST);
    
        $routes = [
            'generateMandatVente' => 'src/forms/saleMandateForm.php',
            'generateProcurationSignature' => 'src/pdf/generateSignatureAuthPDF.php',
            'generateBonReservation' => 'src/forms/reservationForm.php',
            'generateAccordBaissePrix' => 'src/forms/priceReductionForm.php',
            'generateContractEngagement' => 'src/forms/contractEngagementForm.php'
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
                            <input type='hidden' name='client' value='" . htmlspecialchars($selectedCustomers) . "'>
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

            <!-- Clients Acheteur -->
            <div class="content" id="tab1">
                <h2><?= $tableauOnglets[0] ?></h2>
                <div class="input_client">
                    <input type="text" class="searchBar" id="searchBarCustomersBuy" placeholder="Rechercher un client acheteur..." onkeyup="searchTable('CustomersBuy', 'searchBarCustomersBuy')">
                    <?php if($_SESSION['user']['role'] == 1) { ?>
                        <select class="inputSelect" name="selectAgenceBuy" id="selectAgenceBuy" onchange="window.selectAgence('CustomersBuy', 'selectAgenceBuy')">
                            <optgroup label="Choisir l'agence pour trier les clients acheteurs"></optgroup>
                                <option value="All">Toute les agences</option>
                                <?php foreach($resAgence as $agence) { ?>
                                    <option value="<?= $agence['agence_id'] ?>"><?= $agence['agence_nom'] ?></option>
                                <?php } ?>
                            </optgroup>
                        </select>
                    <?php } ?>
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
                                <th>Code postale</th>
                                <th>Numéro CNI</th>
                            </tr>
                        </thead>
                        <tbody class="table-body" id="CustomersBuyTableBody">
                            <!-- INSERT AVEC JS -->
                        </tbody>
                    </table>
                </div>

                <div class="pagination visible" id="paginationCustomersBuy">
                    <a onclick="prevPage('CustomersBuy')">Page précédente</a>
                    <a onclick="nextPage('CustomersBuy')">Page suivante</a>
                    <span id="pageInfoCustomersBuy"></span>
                </div>
            </div>

            <!-- Clients Vendeur -->
            <div class="content" id="tab2">
                <h2><?= $tableauOnglets[1] ?></h2>
                <div class="input_client">
                    <input type="text" class="searchBar" id="searchBarCustomersSell" placeholder="Rechercher un client vendeur..." onkeyup="searchTable('CustomersSell', 'searchBarCustomersSell')">
                    <?php if($_SESSION['user']['role'] == 1) { ?>
                        <select class="inputSelect" name="selectAgenceSell" id="selectAgenceSell" onchange="window.selectAgence('CustomersSell', 'selectAgenceSell')">
                        <optgroup label="Choisir l'agence pour trier les clients vendeurs">
                            <option value="All">Toute les agences</option>
                            <?php foreach($resAgence as $agence) { ?>
                                <option value="<?= $agence['agence_id'] ?>"><?= $agence['agence_nom'] ?></option>
                            <?php } ?>
                        </optgroup>
                        </select>
                    <?php } ?>
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
                                <th>Code postale</th>
                                <th>Numéro CNI</th>
                            </tr>
                        </thead>
                        <tbody class="table-body" id="CustomersSellTableBody">
                            <!-- INSERT AVEC JS -->
                        </tbody>
                    </table>
                </div>

                <div class="pagination visible" id="paginationCustomersSell">
                    <a onclick="prevPage('CustomersSell')">Page précédente</a>
                    <a onclick="nextPage('CustomersSell')">Page suivante</a>
                    <span id="pageInfoCustomersSell"></span>
                </div>
            </div>

            <!-- Véhicules -->
            <div class="content" id="tab3">
                <h2><?= $tableauOnglets[2] ?></h2>
                <div class="input_vehicle">
                    <input type="text" class="searchBar" id="searchBarVehicles" placeholder="Rechercher un véhicule..." onkeyup="searchTable('Vehicles', 'searchBarVehicles')">
                    <?php if($_SESSION['user']['role'] == 1) { ?>
                        <select class="inputSelect" name="selectAgenceVehicles" id="selectAgenceVehicles" onchange="window.selectAgence('Vehicles', 'selectAgenceVehicles')">
                            <optgroup label="Choisir l'agence pour trier les véhicules">
                                <option value="All">Toute les agences</option>
                                <?php foreach($resAgence as $agence) { ?>
                                    <option value="<?= $agence['agence_id'] ?>"><?= $agence['agence_nom'] ?></option>
                                <?php } ?>
                            </optgroup>
                        </select>
                    <?php } ?>
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

                <div class="pagination visible" id="paginationVehicles">
                    <a onclick="prevPage('Vehicles')">Page précédente</a>
                    <a onclick="nextPage('Vehicles')">Page suivante</a>
                    <span id="pageInfoVehicles"></span>
                </div>
            </div>


            <div class="content" id="tab4">
                <h2><?= $tableauOnglets[3] ?></h2>
                <div class="btn_list">
                    <button type="submit" name="generateMandatVente" target="_blank" class="btn-generate action-link">Mandat de vente</button>
                    <button type="submit" name="generateContractEngagement" target="_blank" class="btn-generate action-link">Mandat d'engagement</button>
                    <button type="submit" name="generateProcurationSignature" target="_blank" class="btn-generate action-link">Procuration signature</button>
                    <button type="submit" name="generateBonReservation" target="_blank" class="btn-generate action-link">Bon de réservation</button>
                    <button type="submit" name="generateAccordBaissePrix" target="_blank" class="btn-generate action-link">Accord de baisse du prix net vendeur</button>
                </div>
            </div>
        </div>
    </form>

    <div id="cardItem" class="cardItem hidden">
        <div class="cardItem-content" id="cardItem_content" >
            <!-- INSERT AVEC JS -->
        </div>
    </div>
    
    <script>
        const rowsCustomersSell = [<?= generateRows($resClientVendeur, $customerFields) ?>];
        const rowsCustomersBuy = [<?= generateRows($resClientAcheteur, $customerFields) ?>];
        const rowsVehicles = [<?= generateRows($resVehicule, $vehicleFields) ?>];

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