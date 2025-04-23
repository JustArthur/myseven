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

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../database.php';
    require_once '../functions/createFolderNextCloud.php';
    require_once '../functions/cleanValues.php';
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
                
                <!-- Étape 1 -->
                <div class="form-step" id="step-1">
                    <div class="input_box">
                        <span class="label form_required">Adresse-mail du client</span>
                        <input required type="email" id="customerMail">
                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box">
                        <span class="label form_required">Plaque d'immatriculation</span>
                        <input required type="text" id="immatCar">
                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box button">
                        <button type="button" class="submit_btn" class="next-step" onclick="goToStep(2)">Suivant</button>
                    </div>
                </div>

                <!-- Étape 2 -->
                <div class="form-step" id="step-2" style="display: none;">
                    <div class="input_box">
                        <span class="label form_required">Montant NET Vendeur</span>
                        <input required type="number" name="netVendeur" id="netVendeur">
                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box button">
                        <button type="button" class="submit_btn outline" class="previous-step" onclick="goToStep(1)">Précédent</button>
                        <button type="button" class="submit_btn" class="next-step" onclick="goToStep(3)">Suivant</button>
                    </div>
                </div>

                <!-- Étape 3 -->
                <div class="form-step" id="step-3" style="display: none;">
                    
                    <div class="input_box button">
                        <button type="button" class="submit_btn outline" class="previous-step" onclick="goToStep(2)">Précédent</button>
                    </div>

                    <div class="input_box button">
                        <input class="submit_btn" value="Générer le mandat d'engagement en PDF" type="submit" name="submit_btn" id="submit_btn">
                    </div>
                </div>

            </form>
        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
    <script src="../../assets/js/redirectForm.js"></script>
    <script>
        function goToStep(stepNumber) {
            const allSteps = document.querySelectorAll('.form-step');
            allSteps.forEach((step) => {
                step.style.display = 'none';
            });

            document.getElementById(`step-${stepNumber}`).style.display = 'flex';
        }
    </script>
</body>
</html>