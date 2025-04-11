<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

// Vérification de la session utilisateur
if(!isset($_SESSION['user']['role']) || empty($_COOKIE['user_session'])) {
    header('Location: ../../login.php');
    exit();
}

// Vérifie si il y a bien un idClient et une immatriculation de voiture
if(empty($_GET['idClient']) && empty($_GET['idVehicule'])) {
    header('Location: ../../index.php');
    exit();
}

require_once '../../database.php';
require_once '../functions/createFolderNextCloud.php';
require_once '../functions/cleanValues.php';

$DBB = new ConnexionDB;
$DB = $DBB->openConnection();

$resClient = $DB->prepare("SELECT * FROM clients WHERE clients_id = ?");
$resClient->execute([intval($_GET['idClient'])]);
$resClient = $resClient->fetch();

$resVehicule = $DB->prepare("SELECT * FROM vehicules WHERE vehicules_id = ?");
$resVehicule->execute([intval($_GET['idVehicule'])]);
$resVehicule = $resVehicule->fetch();

$DBB->closeConnection();

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <link rel="stylesheet" href="../../assets/css/forms.css">

    <title>Myseven - Générer une information relative à la vente</title>
</head>

<body id="body">
    <main>

        <div class="search-container">
            <h2>Générer une information relative à la vente</h2>
            <form id="form_pdf" action="../pdf/generateInformationSell.php" method="POST">
                <?php if (!empty($error_message)) {
                    echo "<div style='margin-bottom: 30px;' class='error_message " . $error_message['type'] . "'>" . $error_message['message'] . "</div>";
                } ?>

                <div class="input_box">
                    <span class="label form_required">Type de véhicule</span>
                    <input required="true" name="typeVehicle" type="text" id="typeVehicle">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Numéro de série</span>
                    <input required="true" name="numSerie" type="text" id="numSerie">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <input type="hidden" name="idClient" value="<?= $resClient['clients_id'] ?>">
                <input type="hidden" name="immatCar" value="<?= $resVehicule['vehicules_immatriculation'] ?>">
                <input type="hidden" name="idVehicule" value="<?= $resVehicule['vehicules_id'] ?>">

                <div class="input_box">
                    <input class="submit_btn" type="submit" name="submit_btn" id="submit_btn" value="Générer une information relative à la vente">
                </div>
            </form>
        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
    <script src="../../assets/js/wrapperSelectImmatriculation.js"></script>
</body>

</html>