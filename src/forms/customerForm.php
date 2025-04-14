<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(!isset($_SESSION['user']['role']) || empty($_COOKIE['user_session'])) {
        header('Location: ../../login.php');
        exit();
    }

    $error_message = [];
    $tileCustomer = "";

    switch($_GET['customerType']) {
        case 1:
            $typeCustomerValue = "Acheteur";
            $tileCustomer = "Acheteur";
            break;
        
        case 2:
            $typeCustomerValue = "Vendeur";
            $tileCustomer = "Vendeur";
            break;

        default:
            $typeCustomerValue = "Default";
            $tileCustomer = "";
            break;
    }

    $valid = true;
    $validFolder = true;

    require_once '../../database.php';
    require_once '../functions/createFolderNextCloud.php';
    require_once '../functions/cleanValues.php';

    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['submit_btn'])) {
            $DBB = new ConnexionDB();
            $DB = $DBB->openConnection();

            $getEmail = $DB->prepare("SELECT clients_email FROM clients WHERE clients_email = ?");
            $getEmail->execute([$email]);
            $getEmail = $getEmail->fetch();

            if($getEmail) {
                $valid = false;
                $error_message = [
                    'type' => 'error',
                    'message' => 'L\'adresse mail est déjà utilisée.'
                ];
            }
            
            if (empty($firstName) || empty($lastName) || empty($email) || empty($telephone) || empty($birthday) || empty($lieuNaissance) || empty($numCNI) || empty($adresse) || empty($city) || empty($cp) || empty($_GET['customerType'])) {
                $valid = false;
                $error_message = [
                    'type' => 'error',
                    'message' => 'Tous les champs sont requis.'
                ];
            }

            if($valid) {
                if (isset($_FILES['fileCNI']) && $_FILES['fileCNI']['error'] == 0) {
                    $allowed = ['png', 'jpeg', 'jpg', 'pdf'];
                    $fileInfo = pathinfo($_FILES['fileCNI']['name']);
                    $fileExt = strtolower($fileInfo['extension']);
    
                    if (in_array($fileExt, $allowed)) {
                        $fileContent = file_get_contents($_FILES['fileCNI']['tmp_name']);
    
                        $stmt = $DB->prepare("INSERT INTO clients (clients_nom, clients_prenom, clients_email, clients_telephone, clients_anniversaire, clients_lieu_naissance, clients_numero_cni, clients_copie_cni, clients_rue, clients_ville, clients_cp, clients_agence_id, clients_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([strtoupper($firstName), $lastName, strtolower($email), $telephone, $birthday, $lieuNaissance, $numCNI, $fileContent, $adresse, $city, $cp, intval($_SESSION['user']["agence_id"]), $typeCustomerValue]);
                        
                        if ($stmt->rowCount() > 0) {
                            $getAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
                            $getAgence->execute([intval($_SESSION['user']["agence_id"])]);
                            $getAgence = $getAgence->fetch();

                            $getClient = $DB->prepare('SELECT * FROM clients WHERE clients_email = ?');
                            $getClient->execute([strtolower($email)]);
                            $getClient = $getClient->fetch();

                            // Clean le nom et prénom
                            $cleanFirstName = cleanValue($firstName);
                            $cleanLastName = cleanValue($lastName);
 
                            $folderToCreate = $cleanFirstName . "-" . $cleanLastName;
                            // $createFolderNextcloud = createNextcloudFolder($getAgence['agence_path_client'], $folderToCreate);
                            $createFolderNextcloud = true;

                            if($createFolderNextcloud) {
                                if (isset($_FILES['fileCNI']) && $_FILES['fileCNI']['error'] == 0) {
                                    $tmpPath = $_FILES['fileCNI']['tmp_name'];
                                    $originalFileName = $_FILES['fileCNI']['name'];
                                    $extension = pathinfo($_FILES['fileCNI']['name'], PATHINFO_EXTENSION);

                                    $newFileName = "CNI_{$cleanFirstName}-{$cleanLastName}.{$extension}";
                                    $destinationPath = sys_get_temp_dir() . '/' . $newFileName;
                                
                                    if (move_uploaded_file($tmpPath, $destinationPath)) {
                                        // uploadPdfToNextcloud($getAgence['agence_path_client'], $folderToCreate, $destinationPath);                                
                                        unlink($destinationPath);
    
                                    } else {
                                        $error_message = [
                                            'type' => 'error',
                                            'message' => 'Impossible de déplacer le fichier CNI dans le temp_dir.'
                                        ];

                                        $validFolder = false;
                                    }
                                } else {
                                    $error_message = [
                                        'type' => 'error',
                                        'message' => 'Erreur lors de l\'upload du fichier CNI.'
                                    ];

                                    $validFolder = false;
                                }

                                if($validFolder) {
                                    if($typeCustomerValue == "Acheteur") {
                                        echo '
                                            <form id="redirectForm" action="choiceVehicle.php" method="POST" target="newTabForm" style="display:none;">
                                                <input type="hidden" name="idClient" value="' . strtolower($getClient['clients_id']) . '">
                                            </form>

                                            <script>
                                                window.open("", "newTabForm");
                                                document.getElementById("redirectForm").submit();

                                                window.location.href = "../../index.php";
                                            </script>
                                        ';
                                        exit();
                                    } else {
                                        echo '
                                            <form id="redirectForm" action="vehicleForm.php" method="POST"  target="newTabForm" style="display:none;">
                                                <input type="hidden" name="idClient" value="' . strtolower($getClient['clients_id']) .'">
                                                <input type="hidden" name="customerType" value="' . $_GET['customerType'] .'">
                                            </form>

                                            <script>
                                                window.open("", "newTabForm");
                                                document.getElementById("redirectForm").submit();

                                                window.location.href = "../../index.php";
                                            </script>
                                        ';
                                        exit();
                                    }
                                }
                            } else {
                                $error_message = [
                                    'type' => 'error',
                                    'message' => 'Impossible de créer le dossier client dans le Nextcloud.'
                                ];
                            }
                        } else {
                            $error_message = [
                                'type' => 'error',
                                'message' => 'Impossible de créer le client.'
                            ];
                        }                    
                    } else {
                        $error_message = [
                            'type' => 'error',
                            'message' => 'Fichier CNI invalide. Seuls les fichiers PDF, PNG, JPEG et JPG sont autorisés.'
                        ];
                    }
                } else {
                    $error_message = [
                        'type' => 'error',
                        'message' => 'Fichier CNI trop volumineux. 16Mo maximum.'
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

    <title>Myseven - Créer un client <?= $tileCustomer ?></title>
</head>

<body id="body">
    <main>

        <div class="search-container">
            <h2>Créer un client <?= $tileCustomer ?></h2>
            <form id="form_pdf" method="POST" enctype="multipart/form-data">
                <?php if(!empty($error_message)) {echo "<div style='margin-bottom: 30px;' class='error_message " . $error_message['type'] . "'>" . $error_message['message'] . "</div>"; } ?>

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
                    <input required="true" type="email" name="email" id="email" min="0">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Numéro de téléphone</span>
                    <input required="true" type="number" name="telephone" id="telephone" min="0">

                    <p class="text_error hidden">Ce champ est requis.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Date de naissance</span>
                    <input required="true" type="date" name="birthday" id="birthday" max="<?php echo date('Y-m-d'); ?>">

                    <p class="text_error hidden">Ce champ est incorect.</p>
                </div>

                <div class="input_box">
                    <span class="label form_required">Lieu de naissance</span>
                    <input required="true" type="text" name="lieuNaissance" id="lieuNaissance" min="0">

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
                    <input class="submit_btn" type="submit" name="submit_btn" id="submit_btn" value="Créer le client <?= $tileCustomer ?>">
                </div>
            </form>
        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
</body>

</html>