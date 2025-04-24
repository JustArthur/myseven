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

    if (!isset($_SESSION['user']) || empty($_COOKIE['user_session']) || empty($_SESSION['user']['agence_id'])) {
        echo '
            <script>
                alert("Erreur 403 : Accès interdit. Veuillez vous connecter pour accéder à cette page.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    if(!empty($_POST['emailClientVendeur'])) {

        if (filter_var($_POST['emailClientVendeur'], FILTER_VALIDATE_EMAIL)) {
            $resUpdateClient = $DB->prepare('SELECT clients_id FROM clients WHERE clients_email = ?');
            $resUpdateClient->execute([$_POST['emailClientVendeur']]);
            $resUpdateClient = $resUpdateClient->fetch();
    
            $updateVehicule = $DB->prepare('UPDATE vehicules SET vehicules_clients_id_vendeur = ? WHERE vehicules_immatriculation = ?');
            $updateVehicule->execute([$resUpdateClient['clients_id'], $_POST['immatCar']]);
        } else {
            header('Location: ../../addVendeurToVehicle.php');
            exit();
        }

    }

    if (!empty($_POST['clientEmail'])) {
        $resClientAcheteur = $DB->prepare('SELECT * FROM clients WHERE clients_email = ?');
        $resClientAcheteur->execute([$_POST['clientEmail']]);
        $resClientAcheteur = $resClientAcheteur->fetch();

    } else if (!empty($_POST['idClient'])){
        $resClientAcheteur = $DB->prepare('SELECT * FROM clients WHERE clients_id = ?');
        $resClientAcheteur->execute([$_POST['idClient']]);
        $resClientAcheteur = $resClientAcheteur->fetch();
        
    } else {
        echo '
            <script>
                alert("Impossible de trouver le client acheteur.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }


    if (!$resClientAcheteur || empty($_POST['immatCar'])) {
        echo '
            <script>
                alert("Informations du client acheteur manquantes ou plaque d\'immatriculation non renseignée.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    } else if ($resClientAcheteur['clients_type'] != 'Acheteur') {
        echo '
            <script>
                alert("Attention ce client n\'est pas un acheteur.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }
    
    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    if(!$resVehicule['vehicules_clients_id_vendeur']) {
        echo '
            <form id="redirectForm" action="addVendeurToVehicle.php" method="POST">
                <input type="hidden" name="clientEmailAcheteur" value="' . $resClientAcheteur['clients_email'] . '">
                <input type="hidden" name="immatCar" value="' . $resVehicule['vehicules_immatriculation'] . '">
            </form>

            <script>
                document.getElementById("redirectForm").submit();
            </script>
        ';
        exit();
    }

    $resClientVendeur = $DB->prepare('SELECT * FROM clients WHERE clients_id = ?');
    $resClientVendeur->execute([$resVehicule['vehicules_clients_id_vendeur']]);
    $resClientVendeur = $resClientVendeur->fetch();

    $resClientVendeurCotitulaire = $DB->prepare('SELECT * FROM cotitulaires WHERE cotitulaires_clients_id = ?');
    $resClientVendeurCotitulaire->execute([$resClientVendeur['clients_id']]);
    $resClientVendeurCotitulaire = $resClientVendeurCotitulaire->fetchAll();

    $resClientAcheteurCotitulaire = $DB->prepare('SELECT * FROM cotitulaires WHERE cotitulaires_clients_id = ?');
    $resClientAcheteurCotitulaire->execute([$resClientAcheteur['clients_id']]);
    $resClientAcheteurCotitulaire = $resClientAcheteurCotitulaire->fetchAll();
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
            <div class="progress-container">
                <div class="progress-bar" id="progress-bar"></div>
                <div class="progress-steps">
                    <span class="step-indicator active" data-step="1">1</span>
                    <span class="step-indicator" data-step="2">2</span>
                    <span class="step-indicator" data-step="3">3</span>
                </div>
            </div>
            
            <h2 id="titleForm">Création d'un bon de réservation</h2>
            <form id="form_pdf" target="_blank" action="../pdf/generateTriplePDF.php" method="POST">
                
                <!-- Étape 1 -->
                <div class="form-step" id="step-1">

                    <div class="input_box">
                        <span class="label form_required">Adresse-mail du client vendeur</span>
                        <input required type="email" disabled value="<?= $resClientVendeur['clients_email'] ?>" class="disabled" id="customerMail">

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box">
                        <span class="label form_required">Immatriculation</span>
                        <input required type="text" disabled value="<?= $resVehicule['vehicules_immatriculation'] ?>" class="disabled" id="immatCar">

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

                            <input type="text" name="garantieMecaniqueText" id="inputGarantieMecanique" class="inputPrixGarantie" placeholder="Prix de la garantie" id="garantieMecaniqueText">
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

                            <input required type="text" name="depot_arrhes_input" placeholder="Montant" id="depot_arrhes_input">
                        </div>

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
                    <div class="input_box">
                        <span class="label form_required">Nom du client vendeur</span>
                        <input required type="text" disabled value="<?= $resClientVendeur['clients_nom'] ?>" class="disabled" id="client">
                        <input hidden type="text" name="idClientVendeur" value="<?= $resClientVendeur['clients_id'] ?>">

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box">
                        <span class="label form_required">Prénom du client vendeur</span>
                        <input required type="text" disabled value="<?= $resClientVendeur['clients_prenom'] ?>" class="disabled" id="client">

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <?php if($resClientVendeurCotitulaire) { ?>
                        <div class="input_box">
                            <span class="label">Co-titulaire(s) du client vendeur</span>
                            <ul class="check_list">
                                <?php foreach($resClientVendeurCotitulaire as $cotitulaire) {?>
                                    <li class="check_item">
                                        <input type="checkbox" name="idCotitulaireVendeur[]" id="idCotitulaire_<?= $cotitulaire['cotitulaires_id'] ?>" value="<?= $cotitulaire['cotitulaires_id'] ?>">
                                        <label for="idCotitulaire_<?= $cotitulaire['cotitulaires_id'] ?>"><?= $cotitulaire['cotitulaires_nom'] ?> <?= $cotitulaire['cotitulaires_prenom'] ?></label>
                                    </li>
                                <?php } ?>
                            </ul>

                            <p class="text_error">Ce champ est requis</p>
                        </div>
                    <?php } ?>

                    <div class="input_box">
                        <span class="label form_required">Plaque d'immatriculation de la voiture vendu</span>
                        <input required type="text" disabled  value="<?= $resVehicule['vehicules_immatriculation'] ?>" class="disabled" id="immatCar">
                        <input hidden type="text" name="immatCar" value="<?= $resVehicule['vehicules_immatriculation'] ?>">

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box">
                        <span class="label form_required">Nom du client acheteur</span>
                        <input required type="text" disabled value="<?= $resClientAcheteur['clients_nom'] ?>" class="disabled" id="client">
                        <input hidden type="text" name="idClientAcheteur" value="<?= $resClientAcheteur['clients_id'] ?>">

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box">
                        <span class="label form_required">Prénom du client acheteur</span>
                        <input required type="text" disabled value="<?= $resClientAcheteur['clients_prenom'] ?>" class="disabled" id="client">

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <?php if($resClientAcheteurCotitulaire) { ?>
                        <div class="input_box">
                            <span class="label">Co-titulaire(s) du client acheteur</span>
                            <ul class="check_list">
                                <?php foreach($resClientAcheteurCotitulaire as $cotitulaire) {?>
                                    <li class="check_item">
                                        <input type="checkbox" name="idCotitulaireAcheteur" id="idCotitulaire_<?= $cotitulaire['cotitulaires_id'] ?>" value="<?= $cotitulaire['cotitulaires_id'] ?>">
                                        <label for="idCotitulaire_<?= $cotitulaire['cotitulaires_id'] ?>"><?= $cotitulaire['cotitulaires_nom'] ?> <?= $cotitulaire['cotitulaires_prenom'] ?></label>
                                    </li>
                                <?php } ?>
                            </ul>

                            <p class="text_error">Ce champ est requis</p>
                        </div>
                    <?php } ?>

                    <div class="input_box">
                        <span class="label form_required">Notes du client acheteur</span>
                        <textarea required name="notesClientAcheteur" id="noteClientAcheteur"></textarea>

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box">
                        <span class="label form_required">Notes du client vendeur</span>
                        <textarea required name="notesClientVendeur" id="noteClientVendeur"></textarea>

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box">
                        <span class="label form_required">Date du CashSentinel</span>
                        <input type="date" required name="dateCashSentinel" id="dateCashSentinel"/>

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box">
                        <span class="label form_required">Date livraison possible</span>
                        <input type="date" required name="dateLivraisonPossible" id="dateLivraisonPossible"/>

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box delaiVente">
                        <div class="spanVente">
                            <span class="label form_required">Garantie</span>
                        </div>
                        
                        <select name="garantie" id="garantie">
                            <option value="allRisk">Tous risques</option>
                            <option value="compelete">Complète</option>
                            <option value="essentiel">Essentiel</option>
                            <option value="mbp">Moteur / Boîte / Pont</option>
                        </select>

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box delaiVente">
                        <div class="spanVente">
                            <span class="label form_required">Garantie Constructeur</span>
                        </div>

                        <div class="inputSelect">
                            <select name="askGarantieConstructeur" id="askGarantieConstructeur">
                                <option value="no">Non</option>
                                <option value="yes">Oui</option>
                            </select>

                            <select class="dureeGarantieConstructeur" name="dureeGarantieConstructeur" id="dureeGarantieConstructeur">
                                <option value="3mois">3 mois</option>
                                <option value="6mois">6 mois</option>
                                <option value="12mois">12 mois</option>
                                <option value="24mois">24 mois</option>
                            </select>
                        </div>

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box delaiVente">
                        <div class="spanVente">
                            <span class="label form_required">Paiement</span>
                        </div>

                        <div class="inputSelect">
                            <select name="typePaiement" id="typePaiement">
                                <option hidden value="none">Choisir le type de paiement</option>
                                <option value="arrhes">Arrhes</option>
                                <option value="avanceInter">Avance sur inter</option>
                            </select>

                            <select class="arrhes" name="arrhes" id="arrhes">
                                <option value="CB">Empreinte C.B.</option>
                                <option value="cheque">Chèque</option>
                            </select>

                            <select class="avanceInter" name="avanceInter" id="avanceInter">
                                <option value="virement">Virement</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>

                        <p class="text_error">Ce champ est requis</p>
                    </div>

                    <div class="input_box button">
                        <button type="button" class="submit_btn outline" class="previous-step" onclick="goToStep(2)">Précédent</button>
                        <input class="submit_btn" value="Générer le dossier de vente" type="submit" name="submit_btn" id="submit_btn">
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
            allSteps.forEach((step) => step.style.display = 'none');
            document.getElementById(`step-${stepNumber}`).style.display = 'flex';

            const progress = (stepNumber - 1) / (allSteps.length - 1) * 100;
            document.getElementById('progress-bar').style.width = `${progress}%`;

            const titleForm = document.getElementById('titleForm');
            if (stepNumber === 1) {
                titleForm.textContent = "Création d'un bon de réservation";
            } else if (stepNumber === 2) {
                titleForm.textContent = "Création d'un mandat d'engagement";
            } else if (stepNumber === 3) {
                titleForm.textContent = "Création d'un dossier de vente";
            }

            const indicators = document.querySelectorAll('.step-indicator');
            indicators.forEach((el, index) => {
                if (index < stepNumber) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });
        }
    </script>
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

        const askGarantieConstructeur = document.getElementById('askGarantieConstructeur');
        const typePaiement = document.getElementById('typePaiement');

        askGarantieConstructeur.addEventListener('change', (e) => {
            const dureeGarantieConstructeur = document.getElementById('dureeGarantieConstructeur');

            if(e.target.value === 'no') {
                dureeGarantieConstructeur.classList.remove('active')
            } else {
                dureeGarantieConstructeur.classList.add('active')
            }
        });

        typePaiement.addEventListener('change', (e) => {
            const arrhes = document.getElementById('arrhes');
            const avanceInter = document.getElementById('avanceInter');

            if(e.target.value === 'arrhes') {
                arrhes.classList.add('active')
                avanceInter.classList.remove('active')
            } else {
                arrhes.classList.remove('active')
                avanceInter.classList.add('active')
            }
        });

        </script>
</body>
</html>