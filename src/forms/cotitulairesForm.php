<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    // Vérifie si l'utilisateur est connecté
    if (empty($_SESSION['user']) || empty($_COOKIE['user_session'])) {
        header('Location: ../../login.php');
        exit();
    }

    // Vérifie si il y a bien un idClient et une immatriculation de voiture
    if(empty($_POST['idClient']) || empty($_POST['immatCar'])) {
        header('Location: ../../index.php');
        exit();
    }

    require_once '../../database.php';
    require_once '../functions/createFolderNextCloud.php';
    require_once '../functions/cleanValues.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $getClientPrincipal = $DB->prepare("SELECT * FROM clients WHERE clients_id = ?");
    $getClientPrincipal->execute([$_POST['idClient']]);
    $getClientPrincipal = $getClientPrincipal->fetch();

    $getVehicule = $DB->prepare("SELECT * FROM vehicules WHERE vehicules_immatriculation = ?");
    $getVehicule->execute([$_POST['immatCar']]);
    $getVehicule = $getVehicule->fetch();

    $error_message = [];
    $valid = true;

    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['submit_btn'])) {

            // Vérifie si tout les champs sont remplis
            if (empty($idClient) || empty($immatCar) || empty($firstName) || empty($lastName) || empty($emailCoTitulaire) || empty($telephone) || empty($numCNI) || empty($adresse) || empty($city) || empty($cp)) {
                $error_message = [
                    'type' => 'error',
                    'message' => 'Tous les champs sont requis.'
                ];
                $valid = false;
            }

            // Vérifie si l'adress mail du co titulaire est valide
            if (!filter_var($emailCoTitulaire, FILTER_VALIDATE_EMAIL)) {
                $error_message = [
                    'type' => 'error',
                    'message' => 'L\'adresse mail n\'est pas valide.'
                ];
                $valid = false;
            }

            // Vérifie si l'immatriculation exsite dans la BDD
            $resVehicules = $DB->prepare("SELECT * FROM vehicules WHERE vehicules_immatriculation = ?");
            $resVehicules->execute([$immatCar]);
            $resVehicules = $resVehicules->fetch();

            if (empty($resVehicules)) {
                $error_message = [
                    'type' => 'error',
                    'message' => 'Aucun véhicule trouvé avec cette immatriculation.'
                ];
                $valid = false;
            }

            // Vérifie si le client existe dans la BDD
            $resClient = $DB->prepare("SELECT * FROM clients WHERE clients_id = ?");
            $resClient->execute([$idClient]);
            $resClient = $resClient->fetch(PDO::FETCH_ASSOC);

            if (empty($resClient)) {
                $error_message = [
                    'type' => 'error',
                    'message' => 'Aucun titulaire trouvé.'
                ];
                $valid = false;
            }

            if (isset($_FILES['fileCNI']) && $_FILES['fileCNI']['error'] == 0) {
                $allowed = ['png', 'jpeg', 'jpg', 'pdf'];
                $fileInfo = pathinfo($_FILES['fileCNI']['name']);
                $fileExt = strtolower($fileInfo['extension']);

                if (in_array($fileExt, $allowed)) {
                    $tmpPath = $_FILES['fileCNI']['tmp_name'];
                    $extension = pathinfo($_FILES['fileCNI']['name'], PATHINFO_EXTENSION);
                    $fileContent = file_get_contents($_FILES['fileCNI']['tmp_name']);

                    $cleanFirstName = cleanValue($firstName);
                    $cleanLastName = cleanValue($lastName);
                    $cleanImmatriculation = cleanValue($resVehicules['vehicules_immatriculation']);
                    $cleanBrand = cleanValue($resVehicules['vehicules_marque']);
                    $cleanModel = cleanValue($resVehicules['vehicules_model']);

                    if ($resClient['clients_type'] == 'Vendeur') {
                        $uploadToVehicule = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/DOCUMENTS_DE_VENTE/CLIENT_VENDEUR/';
                    } else {
                        $uploadToVehicule = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/DOCUMENTS_DE_VENTE/CLIENT_ACHETEUR/';
                    }

                    $fileCNIName = "CNI_{$cleanFirstName}-{$cleanLastName}.{$extension}";
                    $destinationPath = sys_get_temp_dir() . '/' . $fileCNIName;

                    if (move_uploaded_file($tmpPath, $destinationPath)) {
                        $resAgence = $DB->prepare("SELECT * FROM agence WHERE agence_id = ?");
                        $resAgence->execute([$_SESSION['user']['agence_id']]);
                        $getAgence = $resAgence->fetch();

                        //Crée le dossier du co-titulaire
                        $folderToCreate = $cleanFirstName . "-" . $cleanLastName;
                        createNextcloudFolder($getAgence['agence_path_client'], $folderToCreate);

                        // Upload la CNI dans le dossier co-titulaire
                        uploadPdfToNextcloud($getAgence['agence_path_client'], $folderToCreate, $destinationPath);

                        // Upload la CNI dans le dossier véhicule
                        uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $uploadToVehicule, $destinationPath);
                    }
                } else {
                    $error_message = [
                        'type' => 'error',
                        'message' => 'Le fichier n\'est pas valide.'
                    ];
                    $valid = false;
                }
            } else {
                $error_message = [
                    'type' => 'error',
                    'message' => 'Le fichier est trop volumineux (16Mo maximum).'
                ];
                $valid = false;
            }

            // Si valid alors il lance l'insert
            if ($valid) {
                $insertCoTitulaire = $DB->prepare("INSERT INTO cotitulaires (cotitulaires_nom, cotitulaires_prenom, cotitulaires_telephone, cotitulaires_email, cotitulaires_rue, cotitulaires_ville, cotitulaires_cp, cotitulaires_copie_cni, cotitulaires_numero_cni, cotitulaires_vehicules_id, cotitulaires_agence_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insertCoTitulaire->execute([$firstName, $lastName, $telephone, $emailCoTitulaire, $adresse, $city, $cp, $fileContent, $numCNI, intval($resVehicules['vehicules_id']), intval($_SESSION['user']['agence_id'])]);

                echo '
                    <head><link rel="stylesheet" href="../../assets/css/forms.css"></head>
                    <div id="popup" class="modal">
                        <div class="modal-content">
                            <h1>Voulez-vous créer un autre co-titulaire pour ce véhicule ?</h1>
                            <form id="cotitulaireForm" action="cotitulairesForm.php" method="POST">
                                <input type="hidden" name="idClient" value="' . $idClient . '">
                                <input type="hidden" name="immatCar" value="' . strtoupper($immatCar) . '">

                                <div class="button-group">
                                    <button type="submit" onclick="setFormAction(\'cotitulairesForm.php\')" name="yes" value="oui">Oui</button>
                                    <button type="submit" onclick="setFormAction(\'saleMandateForm.php\')" name="no" value="non">Non</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <script>
                        function setFormAction(actionUrl) {
                            document.getElementById(\'cotitulaireForm\').action = actionUrl;
                        }
                    </script>
                ';
                exit();

                unlink($destinationPath);
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets/css/pop_up.css">

    <title>Myseven - Créer un co-titulaire</title>
</head>

<body id="body">
    <main>

        <div class="search-container">
            <h2>Créer un co-titulaire</h2>
            <form id="form_pdf" method="POST" enctype="multipart/form-data">
                <?php if (!empty($error_message)) {
                    echo "<div style='margin-bottom: 30px;' class='error_message " . $error_message['type'] . "'>" . $error_message['message'] . "</div>";
                } ?>

                <div class="input_box">
                    <span class="label form_required">Adresse-mail du titulaire du véhicule</span>
                    <input required="true" class="disabled" disabled type="text" id="emailTitulaire" value="<?= $getClientPrincipal['clients_email'] ?>">
                    <input type="hidden" name="idClient" value="<?= $getClientPrincipal['clients_id'] ?>">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Immtraculation du véhicule</span>
                    <input required="true" class="disabled" disabled type="text" id="immatCar" value="<?= $getVehicule['vehicules_immatriculation'] ?>">
                    <input type="hidden" name="immatCar" value="<?= $getVehicule['vehicules_immatriculation'] ?>">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Nom de famille</span>
                    <input required="true" type="text" name="firstName" id="firstName">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Prénom</span>
                    <input required="true" type="text" name="lastName" id="lastName" min="0">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Adresse mail</span>
                    <input required="true" type="email" name="emailCoTitulaire" id="emailCoTitulaire" min="0">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Numéro de téléphone</span>
                    <input required="true" type="number" name="telephone" id="telephone" min="0">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Numéro CNI</span>
                    <input required="true" type="text" id="numCNI" name="numCNI" min="0">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Ajouter la CNI (png, jpg, jpeg, pdf)</span>
                    <input required="true" type="file" id="fileCNI" name="fileCNI" accept=".jpg, .jpeg, .png, .pdf">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Adresse</span>
                    <input required="true" type="text" id="adresse" name="adresse" min="0">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Ville</span>
                    <input required="true" type="text" id="city" name="city" min="0">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Code Postal</span>
                    <input required="true" type="number" id="cp" name="cp" min="0">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <input class="submit_btn" type="submit" name="submit_btn" id="submit_btn" value="Créer le co-titulaire">
                </div>
            </form>
        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
</body>

</html>