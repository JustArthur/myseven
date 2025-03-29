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
                    $stmt = $DB->prepare("INSERT INTO vehicules (vehicules_marque, vehicules_model, vehicules_immatriculation, vehicules_puissance, vehicules_type_boite, vehicules_couleur, vehicules_finition, vehicules_kilometrage, vehicules_annee, vehicules_date_entretien, vehicules_frais_prevoir, vehicules_frais_recent, vehicules_agence_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
                    $stmt->execute([$brand, $model, $immatriculation, $puissance, $type_boite_value, $color, $finition, $kilometrage, $annee, $date_entretien, $frais_prevoir, $frais_recent , intval($_SESSION['user']["agence_id"])]);

                    if ($stmt->rowCount() > 0) {
                        $getAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
                        $getAgence->execute([intval($_SESSION['user']["agence_id"])]);
                        $getAgence = $getAgence->fetch();

                        $folderToCreate = strtoupper($brand) . '/' . strtoupper($model) . '-' . strtoupper($immatriculation) . '/';
                        $createFolderNextcloud = createNextcloudFolder($getAgence['agence_path_vehicules'], $folderToCreate);
                        
                        if($createFolderNextcloud) {
                            if(!empty($_POST['cient_email'])) {
                                echo '
                                    <form id="redirectForm" action="saleMandateForm.php" method="POST">
                                        <input type="hidden" name="client" value="' . strtolower($cient_email) .'">
                                        <input type="hidden" name="immatCar" value="' . strtoupper($immatriculation) .'">
                                    </form>
                                    <script>
                                        document.getElementById("redirectForm").submit();
                                    </script>
                                ';
                            }
                            
                            exit();
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

                <?php if(!empty($_POST['cient_email'])) { echo "<input type='hidden' name='cient_email' value='" . $_POST['cient_email'] . "'>"; } ?>

                <div class="input_box">
                    <span class="label form_required">Immatriculation</span>
                    <input required="true" name="immatriculation" type="text" id="immatriculation">

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
                    <input required="true" type="number" id="kilometrage" name="kilometrage">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Année du véhicule</span>
                    <input required="true" type="number" id="annee" name="annee">

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