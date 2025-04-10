<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    require_once '../../database.php';

    if(!isset($_SESSION['user']['role']) || empty($_COOKIE['user_session'])) {
        header('Location: ../../login.php');
        exit();
    }

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
        header('Location: ../../login.php');
        exit();
    }


    if(!$resClient || empty($_POST['immatCar'])) {
        header('Location: ../../login.php');
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

    <title>Myseven - Générer un bon de réservation</title>
</head>
<body>
    <main>

    <div class="search-container">
        <h2>Générer un bon de réservation</h2>
        <form id="form_pdf" action="../pdf/generateReservationPDF.php" method="POST">
            <div class="input_box">
                <span class="label form_required">Adresse-mail du client</span>
                <input required type="email" disabled  value="<?= $resClient['clients_email'] ?>" class="disabled" id="customerMail">
                <input hidden="true" type="text" name="idClient" value="<?= $resClient['clients_id'] ?>">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label form_required">Immatriculation</span>
                <input required type="text" disabled value="<?= $resVehicule['vehicules_immatriculation'] ?>" class="disabled" id="immatCar">
                <input hidden="true" type="text" name="immatCar" value="<?= $resVehicule['vehicules_immatriculation'] ?>">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label form_required">Prix véhicule seul</span>
                <input required type="text" name="PrixVehicule" id="prixVehicule">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label form_required">Frais de carte grise</span>
                <input required type="text" name="fraisGC" id="fraisGC">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label form_required">Livraison</span>
                <input required type="text" name="livraison" id="livraison">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label">Frais de mise à la route et de courtage</span>
                <input type="text" name="fraisMiseEnRoute" id="fraisMiseEnRoute">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box delaiVente">
                <div class="spanVente">
                    <span class="label form_required">Garantie mécanique souscrite</span>
                </div>

                <div class="inputSelect">
                    <select name="garantieMecaniqueType" id="garantieMecaniqueType">
                        <option value="refuse">Extension de garantie refusée par le client</option>
                        <option value="3Mois">3 mois</option>
                        <option value="12Mois">12 Mois</option>
                        <option value="12MoisPrestige">12 Mois prestige</option>
                        <option value="24Mois">24 Mois</option>
                    </select>

                    <input type="number" min="1" name="garantieMecaniqueText" id="inputGarantieMecanique" class="inputPrixGarantie" placeholder="Prix de la garantie" id="garantieMecaniqueText">
                </div>

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label form_required">Expertise souhaitée</span>

                <select name="expertiseSouhaitee" id="expertiseSouhaitee">
                    <option value="Non">Non</option>
                    <option value="Oui">Oui</option>
                </select>

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box delaiVente">
                <div class="spanVente">
                    <span class="label form_required">Dépot arrhes</span>
                </div>

                <div class="inputSelect">
                    <select name="depot_arrhes_select" id="depot_arrhes_select">
                        <option value="empBank">Empreinte Bancaire</option>
                        <option value="virBank">Virement Bancaire</option>
                        <option value="cheqEsp">Chèque ou Espèce</option>
                    </select>

                    <input required type="number" min="1" name="depot_arrhes_input" placeholder="Montant" id="depot_arrhes_input">
                </div>

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <input class="submit_btn" value="Générer le bon de réservation en PDF" type="submit" name="submit_btn" id="submit_btn">
            </div>
        </form>

    </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
    <script>

        const garantieMecaniqueType = document.getElementById('garantieMecaniqueType');

        garantieMecaniqueType.addEventListener('change', (e) => {
            const inputGarantieMecanique = document.getElementById('inputGarantieMecanique');

            if(e.target.value === 'refuse') {
                inputGarantieMecanique.classList.remove('active')
            } else {
                inputGarantieMecanique.classList.add('active')
            }
        });

    </script>
</body>
</html>