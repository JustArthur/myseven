<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if (!isset($_SESSION['user']) || empty($_COOKIE['user_session']) || empty($_SESSION['user']['agence_id'])) {
        echo '
            <script>
                alert("Erreur 403 : Accès interdit. Veuillez vous connecter pour accéder à cette page.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }

    require_once '../../database.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    if (!empty($_POST['clientEmail'])) {
        $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_email = ?');
        $resClient->execute([$_POST['clientEmail']]);
        $resClient = $resClient->fetch();

    } else if(!empty($_POST['idClient'])) {
        $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_id = ?');
        $resClient->execute([$_POST['idClient']]);
        $resClient = $resClient->fetch();
    } else {
        echo '
            <script>
                alert("Impossible de trouver le client.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }


    if(!$resClient || empty($_POST['immatCar'])) {
        echo '
            <script>
                alert("Impossible de trouver le client ou la plaque d\'immatriculation est invalide.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }

    $resVehicule = $DB->prepare("SELECT * FROM vehicules WHERE vehicules_immatriculation = ?");
    $resVehicule->execute([$_POST['immatCar']]);
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
            <form id="form_pdf" target="_blank" action="../pdf/generateInformationSell.php" method="POST">
                <?php if (!empty($error_message)) {
                    echo "<div style='margin-bottom: 30px;' class='error_message " . $error_message['type'] . "'>" . $error_message['message'] . "</div>";
                } ?>

                <?php if(empty($resVehicule['vehicules_type'])) {?>
                    <div class="input_box">
                        <span class="label form_required">Type de véhicule</span>
                        <input required="true" type="text" name="typeVehicle" id="typeVehicle">

                        <p class="text_error hidden">Ce champ est requis.</p>
                    </div>
                <?php } ?>

                <?php if(empty($resVehicule['vehicules_type'])) {?>
                    <div class="input_box">
                        <span class="label form_required">Numéro de série</span>
                        <input required="true" type="text" name="numSerie" id="numSerie">

                        <p class="text_error hidden">Ce champ est requis.</p>
                    </div>
                <?php } ?>

                <div class="input_box">
                    <span class="label form_required">Date du dernier controle technique</span>
                    <input required="true" type="date" name="dernierControleTechnique" id="dernierControleTechnique">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Kilometrage du véhicule</span>
                    <input required="true" type="text" name="kilometrage" id="kilometrage">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Puissance fiscale</span>
                    <input required="true" type="text" name="puissanceFiscale" id="puissanceFiscale">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Numero PV</span>
                    <input required="true" type="text" name="pvNum" id="pvNum">

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
    <script src="../../assets/js/redirectForm.js"></script>
</body>

</html>