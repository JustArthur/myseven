<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require_once("connexionDB.php");

    $tableauOnglets = [
        'Clients',
        'Véhicules',
        'Générer des PDF'
    ];

    $i = 1;

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();

    // require_once 'vendor/autoload.php';
    // use Dotenv\Dotenv;
    // $dotenv = Dotenv::createImmutable(__DIR__);
    // $dotenv->load();

    // $nextcloudUrl = $_ENV['NEXT_CLOUD_URL'];
    // $clientId = $_ENV['NEXT_CLOUD_CLIENT_ID'];
    // $clientSecret = $_ENV['NEXT_CLOUD_CLIENT_SECRET'];
    // $redirectUri = $_ENV['NEXT_CLOUD_REDIRECT_URI'];

    // var_dump($_GET['code']);

    // if (isset($_GET['code'])) {
    //     $code = $_GET['code'];

    //     $tokenUrl = "$nextcloudUrl/apps/oauth2/api/v1/token";
    //     $data = [
    //         "grant_type" => "authorization_code",
    //         "client_id" => $clientId,
    //         "client_secret" => $clientSecret,
    //         "redirect_uri" => $redirectUri,
    //         "code" => $code
    //     ];

    //     $options = [
    //         "http" => [
    //             "header" => "Content-Type: application/x-www-form-urlencoded",
    //             "method" => "POST",
    //             "content" => http_build_query($data)
    //         ]
    //     ];
    //     $context = stream_context_create($options);
    //     $response = file_get_contents($tokenUrl, false, $context);
    //     $tokenInfo = json_decode($response, true);

    //     if (isset($tokenInfo['access_token'])) {
    //         $accessToken = $tokenInfo['access_token'];

    //         $userInfoUrl = "$nextcloudUrl/ocs/v2.php/cloud/user?format=json";
    //         $opts = [
    //             "http" => [
    //                 "header" => "Authorization: Bearer $accessToken"
    //             ]
    //         ];
    //         $context = stream_context_create($opts);
    //         $userInfoResponse = file_get_contents($userInfoUrl, false, $context);
    //         $userInfo = json_decode($userInfoResponse, true);

    //         echo "Bienvenue, " . htmlspecialchars($userInfo['ocs']['data']['displayname']);
    //     } else {
    //         echo "Erreur d'authentification.";
    //     }
    // }
    //  else {
    //     header("Location: ./php/login.php");
    // }

    $resClient = $DB->prepare('SELECT * FROM Clients ORDER BY nom ASC');
    $resClient->execute();
    $resClient = $resClient->fetchAll();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules ORDER BY immatriculation ASC');
    $resVehicule->execute();
    $resVehicule = $resVehicule->fetchAll();

    $DBClose = $DBB->closeConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        extract($_POST);
    
        $routes = [
            'generateMandatVente' => 'generateMandatVente.php',
            'generateProcurationSignature' => 'generateProcurationSignature.php',
            'generateBonReservation' => 'generateBonReservation.php',
            'generateAccordBaissePrix' => 'generateAccordBaissePrix.php'
        ];

        if (empty($selectedCustomer) || empty($selectedVehicule)) {
            echo "<script>
                    alert('Veuillez sélectionner un client et un véhicule avant de continuer.');
                  </script>";
        } else {
            foreach ($routes as $key => $file) {
                if (isset($_POST[$key])) {
                    echo "
                        <form style='display:none' id='postForm' action='./php/generatePDF/$file' target='_blank' method='POST'>
                            <input type='hidden' name='client' value='" . htmlspecialchars($selectedCustomer) . "'>
                            <input type='hidden' name='immatCar' value='" . htmlspecialchars($selectedVehicule) . "'>
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

    <title>Admin Panel</title>
</head>
<body>
    <form method="POST">
        <!-- <div class="login">
            <?php if (!empty($_SESSION['user'])) { ?>
                <a href="./php/logout.php" class="login-button deco">Se deconnecter</a>
            <?php } else {
                // header('Location: ./php/login.php');
            } ?>
        </div> -->

        <div class="tableau">
            <div class="navbar">
                <?php foreach($tableauOnglets as $onglet) { ?>
                    <a class="tab-button <?php if($i === 1) { echo 'active'; } ?>" data-tab="tab<?= $i ?>"><?= $onglet ?></a>
                <?php $i++; } ?>
            </div>

            <!-- Clients -->
            <div class="content active" id="tab1">
                <h2><?= $tableauOnglets[0] ?></h2>
                <input type="text" class="searchBar" id="searchBarCustomer" placeholder="Rechercher un client..." onkeyup="searchCustomers()">

                <div class="overflowTable">
                    <table class="table" id="customersTable">
                        <thead class="table-head" id="customersTableHead">
                            <tr class="table-row" id="customersTableHeadRow">
                                <th>Nom de famille</th>
                                <th>Prénom</th>
                                <th>Adresse-mail</th>
                                <th>Téléphone</th>
                                <th>Adresse Postal</th>
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
                    // Échapper les retours à la ligne et autres caractères spéciaux
                    $lastName = addslashes($client['nom']);
                    $firstName = addslashes($client['prenom']);
                    $email = addslashes($client['email']);
                    $phone = addslashes($client['telephone']);
                    $address = addslashes($client['adresse'] . " " . $client['ville'] . " " . $client['cp']);
                    $numCNI = addslashes($client['numero_cni']);
                    
                    // Remplacer les retours à la ligne par des espaces ou échappement
                    $lastName = str_replace(["\n", "\r"], " ", $lastName);
                    $firstName = str_replace(["\n", "\r"], " ", $firstName);
                    $email = str_replace(["\n", "\r"], " ", $email);
                    $phone = str_replace(["\n", "\r"], " ", $phone);
                    $address = str_replace(["\n", "\r"], " ", $address);
                    $numCNI = str_replace(["\n", "\r"], " ", $numCNI);

                    // Ajout du client au tableau
                    $itemsCustomer[] = 
                    "{
                        lastName: \"$lastName\",
                        firstName: \"$firstName\",
                        email: \"$email\",
                        phone: \"$phone\",
                        adress: \"$address\",
                        numCNI: \"$numCNI\"
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
                        puissance: \"" . addslashes($vehicule['puissance']) . "\",
                        type_boite: \"" . addslashes($vehicule['type_boite']) . "\",
                        couleur: \"" . addslashes($vehicule['couleur']) . "\",
                        kilometrage: \"" . addslashes($vehicule['kilometrage']."km") . "\"
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