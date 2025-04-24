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

    $resVehicule = $DB->prepare("SELECT * FROM vehicules WHERE vehicules_immatriculation = ?");
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $error_message = [
        'type' => 'warning',
        'message' => 'Ce véhicule n\'as pas de client vendeur associé. Veuillez le lier à un client vendeur avant de continuer.'
    ];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../assets/css/forms.css">

    <title>Myseven - Ajouter le client vendeur du véhicule</title>
</head>
<body>
    <main>
        <div class="search-container">
            <h2>Ajouter le client vendeur du véhicule</h2>
            <form id="form_pdf" action="tripleForm.php" method="POST">
                <?php if(!empty($error_message)) {echo "<div style='margin-bottom: 30px;' class='error_message " . $error_message['type'] . "'>" . $error_message['message'] . "</div>"; } ?>
                
                <div class="input_box">
                    <span class="label form_required">Plaque d'immatriculation du véhicule</span>
                    <input required type="text" disabled  value="<?= $resVehicule['vehicules_immatriculation'] ?>" class="disabled" id="immatCar">
                    <input hidden type="text" name="immatCar" value="<?= $resVehicule['vehicules_immatriculation'] ?>">
                    
                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Marque du véhicule</span>
                    <input required type="text" disabled  value="<?= $resVehicule['vehicules_marque'] ?>" class="disabled" id="marqueCar">
                    
                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Modèle du véhicule</span>
                    <input required type="text" disabled  value="<?= $resVehicule['vehicules_model'] ?>" class="disabled" id="modelCar">
                    
                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Couleur du véhicule</span>
                    <input required type="text" disabled value="<?= $resVehicule['vehicules_couleur'] ?>" class="disabled" id="colorCar">
                    
                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Adresse-mail du client qui vend ce véhicule</span>
                    <input required type="email" name="emailClientVendeur" id="emailClientVendeur">
                    <input type="hidden" name="clientEmail" value="<?= $_POST['clientEmailAcheteur'] ?>">

                    <p class="text_error">Ce champ est requis</p>
                </div>

                <div class="input_box">
                    <input class="submit_btn" value="Lier ce client à ce véhicule" type="submit" name="submit_btn" id="submit_btn">
                </div>
            </form>

        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
</body>
</html>