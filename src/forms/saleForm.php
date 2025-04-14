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

    if(!isset($_SESSION['user']['role']) || empty($_COOKIE['user_session'])) {
        header('Location: ../../login.php');
        exit();
    }

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    if (!empty($_POST['clientEmail'])) {
        $resClientAcheteur = $DB->prepare('SELECT * FROM clients WHERE clients_email = ?');
        $resClientAcheteur->execute([$_POST['clientEmail']]);
        $resClientAcheteur = $resClientAcheteur->fetch();

    } else if (!empty($_POST['idClient'])){
        $resClientAcheteur = $DB->prepare('SELECT * FROM clients WHERE clients_id = ?');
        $resClientAcheteur->execute([$_POST['idClient']]);
        $resClientAcheteur = $resClientAcheteur->fetch();
        
    } else {
        header('Location: ../../index.php');
        exit();
    }


    if(!$resClientAcheteur || empty($_POST['immatCar'])) {
        header('Location: ../../index.php');
        exit();
    }

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

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

    <title>Myseven - Générer le dossier de vente</title>
</head>
<body>
    <main>
        <div class="search-container">
            <h2>Générer le dossier de vente</h2>
            <form id="form_pdf" action="../pdf/generatePriceReductionPDF.php" method="POST">
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
                        <span class="label">Co-titulaire du client vendeur</span>
                        <ul class="check_list">
                            <?php foreach($resClientVendeurCotitulaire as $cotitulaire) {?>
                                <li class="check_item">
                                    <input type="checkbox" name="idCotitulaireVendeur" id="idCotitulaire_<?= $cotitulaire['cotitulaires_id'] ?>" value="<?= $cotitulaire['cotitulaires_id'] ?>">
                                    <label for="idCotitulaire_<?= $cotitulaire['cotitulaires_id'] ?>"><?= $cotitulaire['cotitulaires_nom'] ?> <?= $cotitulaire['cotitulaires_prenom'] ?></label>
                                </li>
                            <?php } ?>
                        </ul>

                        <p class="text_error">Ce champ est requis</p>
                    </div>
                <?php } ?>

                <div class="input_box">
                    <span class="label form_required">Plaque d'immatriculation de la voiture vendeur</span>
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
                        <span class="label">Co-titulaire du client acheteur</span>
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
                    <input class="submit_btn" value="Générer le dossier de vente" type="submit" name="submit_btn" id="submit_btn">
                </div>
            </form>

        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
</body>
</html>