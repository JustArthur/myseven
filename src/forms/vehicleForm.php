<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if (!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    }

    $error_message = [];
    $valid = true;
    $validFolder = true;

    require_once '../../database.php';
    require_once '../functions/createFolderNextCloud.php';

    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['submit_btn'])) {

            switch($type_boite) {
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
                        
                        $immatriculationCleaned = preg_replace('/[^A-Za-z0-9]/', '-', strtoupper($immatriculation));
                        $toCleanVehicule = strtoupper($brand) . '/'. strtoupper($model) . '-' . strtoupper($immatriculation) . '/';

                        $cleanedValueNameVehicule = $toCleanVehicule . "DOCUMENTS_DE_VENTE/CLIENT_VENDEUR/";
                        $carteGriseUploadNext = $toCleanVehicule . "CARTE_GRISE";

                        $newFileName = "CARTE_GRISE_{$model}-{$immatriculation}.{$extension}";

                        $destinationPath = sys_get_temp_dir() . '/' . $newFileName;
        
                        if (in_array($extension, $allowed)) {
                            $fileContent = file_get_contents($_FILES['fileCarteGrise']['tmp_name']);

                            $stmt = $DB->prepare("INSERT INTO vehicules (vehicules_marque, vehicules_model, vehicules_carte_grise, vehicules_immatriculation, vehicules_puissance, vehicules_type_boite, vehicules_couleur, vehicules_finition, vehicules_kilometrage, vehicules_annee, vehicules_date_entretien, vehicules_frais_prevoir, vehicules_frais_recent, vehicules_agence_id, vehicules_date_mise_en_circu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
                            $stmt->execute([strtoupper($brand), strtoupper($model), $fileContent, $immatriculationCleaned, $puissance, $type_boite_value, $color, $finition, $kilometrage, $annee, $date_entretien, $frais_prevoir, $frais_recent , intval($_SESSION['user']["agence_id"]), $dateMiseEnCircu]);
        
                            if ($stmt->rowCount() > 0) {
                                $getAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
                                $getAgence->execute([intval($_SESSION['user']["agence_id"])]);
                                $getAgence = $getAgence->fetch();
        
                                $brandFolder = preg_replace('/[^A-Za-z0-9]/', '_', strtoupper($brand)) . '/';
                                $createBrandFolder = createNextcloudFolder($getAgence['agence_path_vehicules'], $brandFolder);
        
                                if ($createBrandFolder) {
                                    $folderToCreate = preg_replace('/[^A-Za-z0-9]/', '_', strtoupper($brand)) . '/' . preg_replace('/[^A-Za-z0-9]/', '_', strtoupper($model)) . '-' . $immatriculationCleaned . '/';
                                    $createFolderNextcloud = createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreate);
        
                                    if($createFolderNextcloud) {
                                        $folderToCreateArray = [
                                            'PHOTOS',
                                            'CARTE_GRISE',
                                            'CONTROLE_TECHNIQUE',
                                            'FACTURES',
                                            'DOCUMENTS_DE_VENTE'
                                        ];
        
                                        foreach ($folderToCreateArray as $folder) {
                                            $folderToCreateVehicule = $folderToCreate . $folder;
                                            $createFolderNextcloud = createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreateVehicule);
        
                                            if($createFolderNextcloud) {
                                                $valid = true;
                                            } else {
                                                $valid = false;
                                                $error_message = [
                                                    'type' => 'error',
                                                    'message' => 'Impossible de créer le dossier ' . $folder . '.'
                                                ];
                                                break;
                                            }
                                        }
        
                                        if($valid) {
                                            $folderToCreateArrayClient = [
                                                'CLIENT_VENDEUR',
                                                'CLIENT_ACHETEUR'
                                            ];
        
                                            foreach($folderToCreateArrayClient as $clientFolder) {
                                                $folderToCreateClientDocument = $folderToCreate . $folderToCreateArray[4] . '/' . $clientFolder;
                                                $createFolderNextcloudClient = createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreateClientDocument);

        
                                                if($folderToCreateClientDocument) {
                                                    $valid = true;
                                                } else {
                                                    $valid = false;
                                                    $error_message = [
                                                        'type' => 'error',
                                                        'message' => 'Impossible de créer le dossier ' . $clientFolder . '.'
                                                    ];
                                                    break;
                                                }
                                            }

                                            if (move_uploaded_file($tmpPath, $destinationPath)) {
                                                if(!empty($_GET['customerType'])) {
                                                    switch ($_GET['customerType']) {
                                                        case 2:
                                                            $stmt = $DB->prepare("SELECT * FROM clients WHERE clients_email = ?");
                                                            $stmt->execute([urldecode($_GET['cient_email'])]);
                                                            $resClient = $stmt->fetch();
        
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

                                                            $tempFilePath = sys_get_temp_dir() . "/CNI_client_" . str_replace(' ', '_', strtoupper($resClient['clients_nom'])) . "-" . str_replace(' ', '_', strtoupper($resClient['clients_prenom'])) . ".{$extension}";

                                                            file_put_contents($tempFilePath, $resClient['clients_copie_cni']);
        
                                                            $uploadSuccess = uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $cleanedValueNameVehicule, $tempFilePath);
                                                            // $uploadSuccess = uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $cleanedValueNameVehicule, $destinationPath);
                                                            break;
        
                                                        default:
                                                            break;
                                                    }
                                            
                                                    if ($uploadSuccess) {
                                                        $validFolder = true;
                                                    } else {
                                                        $validFolder = false;
                                                    }


                                                }
                                                
                                                $uploadSuccess_2 = uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $carteGriseUploadNext, $destinationPath);

                                                unlink($destinationPath);
                                                
                                            } else {
                                                $error_message = [
                                                    'type' => 'error',
                                                    'message' => 'Impossible de déplacer le fichier.'
                                                ];
        
                                                $validFolder = false;
                                            }
                                        }
                                    }
        
                                    $valid = true;
                                } else {
                                    $valid = false;
                                }
                                
                                if($valid = true && $validFolder == true) {
                                    if(!empty($_GET['cient_email'])) {
                                        echo '
                                            <form id="redirectForm" action="saleMandateForm.php" method="POST">
                                                <input type="hidden" name="client" value="' . strtolower($cient_email) .'">
                                                <input type="hidden" name="immatCar" value="' . strtoupper($immatriculation) .'">
                                            </form>
                                            <script>
                                                document.getElementById("redirectForm").submit();
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
                <?php if(!empty($error_message)) {echo "<div style='margin-bottom: 30px;' class='error_message " . $error_message['type'] . "'>" . $error_message['message'] . "</div>"; } ?>

                <?php if(!empty($_GET['cient_email'])) { echo "<input type='hidden' name='cient_email' value='" . $_GET['cient_email'] . "'>"; } ?>

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
                    <span class="label form_required">Frais à prévoir</span>
                    <input required="true" type="text" id="frais_prevoir" name="frais_prevoir">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Frais récent</span>
                    <input required="true" type="text" id="frais_recent" name="frais_recent">

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