<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    } 
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
            <form id="form_pdf" action="../pdf/generateContractEngagementPDF.php" method="POST">
                <div class="input_box">
                    <span class="label form_required">Adresse-mail du client</span>
                    <input required type="email" disabled  value="<?= $_POST['client'] ?>" class="disabled" id="customerMail">
                    <input hidden type="email" name="customerMail" value="<?= $_POST['client'] ?>">

                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Plaque d'immatriculation</span>
                    <input required type="text" disabled  value="<?= $_POST['immatCar'] ?>" class="disabled" id="immatCar">
                    <input hidden type="text" name="immatCar" value="<?= $_POST['immatCar'] ?>">

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
</body>
</html>