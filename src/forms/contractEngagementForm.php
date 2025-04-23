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

    } else if (!empty($_POST['idClient'])){
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

    $resVehicule = $DB->prepare("SELECT vehicules_immatriculation FROM vehicules WHERE vehicules_immatriculation = ?");
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../assets/css/forms.css">

    <title>Myseven - Générer un mandat d'engagement</title>
</head>
<body>
    <main>
        <div class="search-container">
            <h2>Générer un mandat d'engagement</h2>
            <form id="form_pdf" target="_blank" action="../pdf/generateContractEngagementPDF.php" method="POST">
                <div class="input_box">
                    <span class="label form_required">Adresse-mail du client</span>
                    <input required type="email" disabled  value="<?= $resClient['clients_email'] ?>" class="disabled" id="customerMail">
                    <input hidden type="text" name="idClient" value="<?= $resClient['clients_id'] ?>">

                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Plaque d'immatriculation</span>
                    <input required type="text" disabled  value="<?= $resVehicule['vehicules_immatriculation'] ?>" class="disabled" id="immatCar">
                    <input hidden type="text" name="immatCar" value="<?= $resVehicule['vehicules_immatriculation'] ?>">

                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Montant NET Vendeur</span>
                    <input required type="number" name="netVendeur" id="netVendeur">

                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <input class="submit_btn" value="Générer le mandat d'engagement en PDF" type="submit" name="submit_btn" id="submit_btn">
                </div>
            </form>

        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
    <script src="../../assets/js/redirectForm.js"></script>
</body>
</html>