<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../php/login.php');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../style/formStyle.css">

    <title>Document</title>
</head>
<body>
    <main>

    <div class="search-container">
        <h2>Générer un bon de réservation</h2>
        <form class="gap" id="form_pdf" action="../generatePDF/generateBonReservation.php" method="POST">
            <div class="input_box">
                <span class="label form_required">Adresse-mail du client</span>
                <input required type="email" disabled  value="<?= $_POST['client'] ?>" class="disabled" id="customerMail">
                <input hidden type="text" name="customerMail" value="<?= $_POST['client'] ?>">
            </div>

            <div class="input_box">
                <span class="label form_required">Immatriculation</span>
                <input required type="text" disabled  value="<?= $_POST['immatCar'] ?>" class="disabled" id="customerMail">
                <input hidden type="text" name="immatCar" value="<?= $_POST['immatCar'] ?>">
            </div>

            <div class="input_box">
                <span class="label form_required">Prix véhicule seul</span>
                <input required type="number" name="PrixVehicule" id="prixVehicule">
            </div>

            <div class="input_box">
                <span class="label form_required">Livraison</span>
                <input required type="number" name="livraison" id="livraison">
            </div>

            <div class="input_box">
                <span class="label form_required">Frais de mise à la route et de courtage</span>

                <select name="fraisMiseEnRoute" id="fraisMiseEnRoute">
                    <option value="Oui">Oui (690 €)</option>
                    <option value="Non">Non</option>
                </select>
            </div>

            <div class="input_box">
                <span class="label form_required">Garantie mécanique souscrite</span>

                <select name="garantieMecanique" id="garantieMecanique">
                    <option selected value="refuse">Extension de garantie refusée par le client</option>
                    <option value="3Mois">3 Mois (Inclus)</option>
                    <option value="12Mois">12 Mois (890 €)</option>
                    <option value="12MoisPrestige">12 Mois prestige (1490 €)</option>
                    <option value="24Mois">24 Mois (1490 €)</option>
                </select>
            </div>

            <div class="input_box">
                <span class="label form_required">Expertise souhaitée</span>

                <select name="expertiseSouhaitee" id="expertiseSouhaitee">
                    <option value="Oui">Oui</option>
                    <option value="Non">Non</option>
                </select>
            </div>

            <div class="input_box">
                <input class="submit_btn" value="Générer le bon de réservation en PDF" type="submit" name="submit_btn" id="submit_btn">
            </div>
        </form>

    </div>
    </main>
</body>
</html>