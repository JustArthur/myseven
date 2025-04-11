<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

if (empty($_SESSION['user']) || empty($_COOKIE['user_session'])) {
    header('Location: ../../login.php');
    exit();
}

$error_message = [];
$valid = true;
$validFolder = true;

require_once '../../database.php';
require_once '../functions/createFolderNextCloud.php';
require_once '../functions/cleanValues.php';

if (!empty($_POST)) {
    extract(array: $_POST);
    if (isset($_POST['submit_btn'])) {

        switch ($type_boite) {
            case 1:
                $type_boite_value = "Manuelle";
                break;

            case 2:
                $type_boite_value = "Automatique";
                break;

            default:
                $type_boite_value = "null";
                break;
        }

        if (empty($immatriculation) || empty($brand) || empty($model) || empty($puissance) || $type_boite_value === "null" || empty($color) || empty($finition) || empty($kilometrage) || empty($annee) || empty($date_entretien) || empty($frais_prevoir) || empty($frais_recent)) {
            $error_message = [
                'type' => 'error',
                'message' => 'Tous les champs sont requis..'
            ];
        } else {
            $DBB = new ConnexionDB();
            $DB = $DBB->openConnection();

            $getImmat = $DB->prepare("SELECT vehicules_immatriculation FROM vehicules WHERE vehicules_immatriculation = ?");
            $getImmat->execute([$immatriculation]);
            $getImmat = $getImmat->fetch();

            if (!$getImmat && $type_boite_value != "null") {

                if (isset($_FILES['fileCarteGrise']) && $_FILES['fileCarteGrise']['error'] == 0) {
                    $allowed = ['png', 'jpeg', 'jpg', 'pdf'];
                    $extension = pathinfo($_FILES['fileCarteGrise']['name'], PATHINFO_EXTENSION);
                    $tmpPath = $_FILES['fileCarteGrise']['tmp_name'];

                    $cleanBrand = cleanValue($brand);
                    $cleanModel = cleanValue($model);
                    $cleanImmatriculation = cleanValue($immatriculation);

                    $toCleanVehicule = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/';
                    $newFileName = "CARTE_GRISE_{$cleanModel}-{$cleanImmatriculation}.{$extension}";

                    $destinationPath = sys_get_temp_dir() . '/' . $newFileName;

                    if (in_array($extension, $allowed)) {
                        $fileContent = file_get_contents($_FILES['fileCarteGrise']['tmp_name']);

                        $stmt = $DB->prepare("INSERT INTO vehicules (vehicules_marque, vehicules_model, vehicules_carte_grise, vehicules_immatriculation, vehicules_puissance, vehicules_type_boite, vehicules_couleur, vehicules_finition, vehicules_kilometrage, vehicules_annee, vehicules_date_entretien, vehicules_frais_prevoir, vehicules_frais_recent, vehicules_agence_id, vehicules_date_mise_en_circu, vehicules_type, vehicules_numero_serie, vehicules_origine, vehicules_nombre_main) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([strtoupper($brand), strtoupper($model), $fileContent, strtoupper($immatriculation), $puissance, $type_boite_value, $color, $finition, $kilometrage, $annee, $date_entretien, $frais_prevoir, $frais_recent, intval($_SESSION['user']["agence_id"]), $dateMiseEnCircu, $typeVehicle, $numSerie, $originCar, $nbrMains]);

                        if ($stmt->rowCount() > 0) {
                            $getAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
                            $getAgence->execute([intval($_SESSION['user']["agence_id"])]);
                            $getAgence = $getAgence->fetch();

                            $brandFolder = $cleanBrand . '/';
                            $createBrandFolder = createNextcloudFolder($getAgence['agence_path_vehicules'], $brandFolder);

                            $folderToCreate = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/';
                            $createFolderNextcloud = createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreate);

                            $folderToCreatePhoto = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/PHOTOS/';
                            $folderToCreateCarteGrise = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/CARTE_GRISE/';
                            $folderToCreateControleTechnique = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/CONTROLE_TECHNIQUE/';
                            $folderToCreateFactures = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/FACTURES/';
                            $folderToCreateDocumentDeVente = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/DOCUMENTS_DE_VENTE/';

                            createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreatePhoto);
                            createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreateCarteGrise);
                            createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreateControleTechnique);
                            createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreateFactures);
                            createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreateDocumentDeVente);

                            $folderToCreateClientVendeur = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/DOCUMENTS_DE_VENTE/CLIENT_VENDEUR/';
                            $folderToCreateClientAcheteur = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/DOCUMENTS_DE_VENTE/CLIENT_ACHETEUR/';

                            createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreateClientVendeur);
                            createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreateClientAcheteur);

                            if (move_uploaded_file($tmpPath, $destinationPath)) {
                                if (!empty($customerType)) {
                                    switch ($customerType) {
                                        case 2:
                                            $resClient = $DB->prepare("SELECT * FROM clients WHERE clients_id = ?");
                                            $resClient->execute([$idClient]);
                                            $resClient = $resClient->fetch();

                                            $cleanFirstName = cleanValue($resClient['clients_prenom']);
                                            $cleanLastName = cleanValue($resClient['clients_nom']);

                                            $fileContent = $resClient['clients_copie_cni'];

                                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                                            $mimeType = $finfo->buffer($resClient['clients_copie_cni']);

                                            $extension = match ($mimeType) {
                                                'image/jpeg' => 'jpg',
                                                'image/png' => 'png',
                                                'image/gif' => 'gif',
                                                'image/webp' => 'webp',
                                                'application/pdf' => 'pdf',
                                                default => 'pdf'
                                            };

                                            $tempFilePath = sys_get_temp_dir() . "/CNI_" . $cleanLastName . "-" . $cleanFirstName . ".{$extension}";

                                            file_put_contents($tempFilePath, $resClient['clients_copie_cni']);

                                            uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $folderToCreateClientVendeur, $tempFilePath);
                                            break;

                                        default:
                                            break;
                                    }
                                }

                                $carteGriseUploadNext = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/CARTE_GRISE/';
                                uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $carteGriseUploadNext, $destinationPath);
                                unlink($destinationPath);
                            } else {
                                $error_message = [
                                    'type' => 'error',
                                    'message' => 'Impossible de déplacer le fichier.'
                                ];

                                $validFolder = false;
                            }

                            $valid = true;

                            if ($valid = true && $validFolder == true) {
                                if (!empty($idClient)) {
                                    echo '
                                        <head><link rel="stylesheet" href="../../assets/css/forms.css"></head>
                                        <div id="popup" class="modal">
                                            <div class="modal-content">
                                                <h1>Voulez-vous créer un co-titulaire pour ce véhicule?</h1>
                                                <form id="cotitulaireForm" action="cotitulairesForm.php" method="POST">
                                                    <input type="hidden" name="idClient" value="'. $idClient .'">
                                                    <input type="hidden" name="immatCar" value="'. strtoupper($immatriculation) .'">

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
                                } else {
                                    header("Location: ../../index.php");
                                    exit;
                                }
                            } else {
                                $error_message = [
                                    'type' => 'error',
                                    'message' => 'Impossible de créer le dossier véhicule dans le Nextcloud.'
                                ];
                            }
                        } else {
                            $error_message = [
                                'type' => 'error',
                                'message' => 'Impossible de créer le véhicule.'
                            ];
                        }
                    }
                } else {
                    $error_message = [
                        'type' => 'error',
                        'message' => 'Fichier carte grise trop volumineux. 16Mo maximum.'
                    ];
                }
            } else {
                $error_message = [
                    'type' => 'error',
                    'message' => 'L\'immatriculation existe déjà.'
                ];
            }
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

    <title>Myseven - Créer un véhicule</title>
</head>

<body id='body'>
    <main>

        <div class="search-container">
            <h2>Créer un véhicule</h2>
            <form id="form_pdf" method="POST" enctype="multipart/form-data">
                <?php if (!empty($error_message)) {
                    echo "<div style='margin-bottom: 30px;' class='error_message " . $error_message['type'] . "'>" . $error_message['message'] . "</div>";
                } ?>

                <?php if (!empty($_POST['idClient'])) {
                    echo "<input type='hidden' name='idClient' value='" . $_POST['idClient'] . "'>";
                    echo "<input type='hidden' name='customerType' value='" . $_POST['customerType'] . "'>";
                } ?>

                <div class="input_box">
                    <span class="label form_required">Immatriculation</span>
                    <input required="true" name="immatriculation" type="text" id="immatriculation">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Carte grise (png, jpg, jpeg, pdf)</span>
                    <input required="true" type="file" id="fileCarteGrise" name="fileCarteGrise" accept=".png, .jpeg, .jpg, .pdf">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Marque</span>
                    <input required="true" name="brand" type="text" id="brand">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Modèle</span>
                    <input required="true" name="model" type="text" id="model">

                    <p class="text_error hidden">Ce champ est requis.</p>
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
                    <span class="label form_required">Type de véhicule</span>
                    <input required="true" name="typeVehicle" type="text" id="typeVehicle">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Numéro de série</span>
                    <input required="true" name="numSerie" type="text" id="numSerie">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Puissance</span>
                    <input required="true" name="puissance" type="number" id="puissance">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Type de boite</span>

                    <select required="true" name="type_boite" id="type_boite">
                        <option value=0>-- Choisir le type de boite --</option>
                        <option value=1>Manuelle</option>
                        <option value=2>Automatique</option>
                    </select>

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Couleur du véhicule</span>
                    <input required="true" type="text" id="color" name="color">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Finition du véhicule</span>
                    <input required="true" type="text" id="finition" name="finition">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Kilometrage</span>
                    <input required="true" type="text" id="kilometrage" name="kilometrage">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Année du véhicule</span>
                    <input required="true" type="number" id="annee" name="annee">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Date de mise en circulation</span>
                    <input required="true" type="date" id="dateMiseEnCircu" name="dateMiseEnCircu">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Date entretien</span>
                    <input required="true" type="date" id="date_entretien" name="date_entretien">

                    <p class="text_error hidden">Ce champ est incorect.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Frais récent</span>
                    <input required="true" type="text" id="frais_recent" name="frais_recent">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Frais à prévoir</span>
                    <input required="true" type="text" id="frais_prevoir" name="frais_prevoir">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <input class="submit_btn" value="Créer le véhicule" type="submit" name="submit_btn" id="submit_btn">
                </div>
            </form>

        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
</body>

</html>