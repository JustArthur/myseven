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
<html lang="en">
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
                <input required type="email" disabled  value="<?= $_POST['client'] ?>" class="disabled" id="customerMail">
                <input hidden="true" type="text" name="customerMail" value="<?= $_POST['client'] ?>">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label form_required">Immatriculation</span>
                <input required type="text" disabled  value="<?= $_POST['immatCar'] ?>" class="disabled" id="immatCar">
                <input hidden="true" type="text" name="immatCar" value="<?= $_POST['immatCar'] ?>">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label form_required">Prix véhicule seul</span>
                <input required type="number" name="PrixVehicule" id="prixVehicule">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label form_required">Date de mise en circulation</span>
                <input required type="date" name="miseCircu" id="miseCircu">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label form_required">Livraison</span>
                <input required type="number" name="livraison" id="livraison">

                <p class="text_error">Ce champ est requis</p>
            </div>

            <div class="input_box">
                <span class="label">Frais de mise à la route et de courtage</span>
                <input type="number" name="fraisMiseEnRoute" id="fraisMiseEnRoute">

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