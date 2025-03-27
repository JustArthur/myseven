<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    }

    if(empty($_POST['client'])) { header('Location: ../../index.php'); exit(); }
    if(empty($_POST['immatCar'])) { $_POST['immatCar'] = $_GET['vehicle_immat'] ?? ''; }

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../assets/css/forms.css">

    <title>Myseven - Générer un mandat de vente</title>
</head>
<body>
    <main>
        <div class="search-container">
            <h2>Générer un mandat de vente</h2>
            <form id="form_pdf" action="../pdf/generateSaleMandatePDF.php" method="POST">
                <div class="input_box">
                    <span class="label form_required">Adresse-mail du client</span>
                    <input required type="email" disabled name="customerMail"  value="<?= $_POST['client'] ?>" class="disabled" id="customerMail">
                    <input hidden type="text" name="customerMail" value="<?= $_POST['client'] ?>">

                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Immatriculation</span>
                    <input required type="text" name="immatricuCar" value="<?= $_POST['immatCar'] ?>" id="immatricuCar">
                    <!-- <input hidden type="text" name="immatricuCar" value="<?= $_POST['immatCar'] ?>"> -->

                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Origine du véhicule</span>
                    <input required type="text" name="originCar" id="originCar">

                    <p class="text_error hidden">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Nombre de mains</span>
                    <input required type="text" name="nbrMains" id="nbrMains">

                    <p class="text_error hidden">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Jour de la visite</span>
                    <select required name="jourVisite" id="jourVisite">
                        <option value="Lundi">Lundi</option>
                        <option value="Mardi">Mardi</option>
                        <option value="Mercredi">Mercredi</option>
                        <option value="Jeudi">Jeudi</option>
                        <option value="Vendredi">Vendredi</option>
                        <option value="Samedi">Samedi</option>
                        <option value="Dimanche">Dimanche</option>
                    </select>

                    <p class="text_error hidden">Ce champ est requis</p>
                </div>

                

                <div class="input_box">
                    <span class="label form_required">Raison de la vente</span>
                    <input required type="text" name="raisonVente" id="raisonVente">

                    <p class="text_error hidden">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Prix de vente NET souhaité</span>
                    <input required type="text" name="prixVenteSouhaite" id="inputEuroVenteSouhaite">

                    <p class="text_error hidden">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Prix de vente constaté sur le marché</span>
                    <input required type="text" name="prixVente" id="inputEuroPrixVente">

                    <p class="text_error hidden">Ce champ est requis</p>
                </div>

                <div class="input_box delaiVente">
                    <div class="spanVente">
                        <span class="label form_required">Delai de vente</span>
                    </div>

                    <div class="inputSelect">
                        <input required type="number" min="1" name="delayVenteText" id="delayVenteText">

                        <select name="delayVenteType" id="delayVenteType">
                            <option value="Jour(s)">Jour(s)</option>
                            <option value="Mois">Mois</option>
                            <option value="An(s)">An(s)</option>
                        </select>
                    </div>

                    <p class="text_error hidden">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <input class="submit_btn" value="Générer le mandat de vente en PDF" type="submit" name="submit_btn" id="submit_btn">
                </div>
            </form>

        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
    <script src="../../assets/js/pricing.js"></script>
</body>
</html>