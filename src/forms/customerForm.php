<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if (!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    }

    require_once '../../database.php';

    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['submit_btn'])) {
            $DBB = new ConnexionDB();
            $DB = $DBB->openConnection();

            switch($typeCustomer) {
                case 1:
                    $typeCustomerValue = "Acheteur";
                    break;

                case 2:
                    $typeCustomerValue = "Vendeur";
                    break;

                default:
                    $typeCustomerValue = "null";
                    break;
            }

            $getEmail = $DB->prepare("SELECT clients_email FROM clients WHERE clients_email = ?");
            $getEmail->execute([$email]);
            $getEmail = $getEmail->fetch();

            if($getEmail) {
                echo '
                    <script>
                        window.alert("L\'adress mail est déjà utilisé");
                    </script>
                ';
            } else if (isset($_FILES['fileCNI']) && $_FILES['fileCNI']['error'] == 0) {
                $allowed = ['png', 'jpeg', 'jpg', 'pdf'];
                $fileInfo = pathinfo($_FILES['fileCNI']['name']);
                $fileExt = strtolower($fileInfo['extension']);

                if (in_array($fileExt, $allowed)) {
                    $fileContent = file_get_contents($_FILES['fileCNI']['tmp_name']);

                    $stmt = $DB->prepare("INSERT INTO clients (clients_nom, clients_prenom, clients_email, clients_telephone, clients_anniversaire, clients_lieu_naissance, clients_numero_cni, clients_copie_cni, clients_rue, clients_ville, clients_cp, clients_agence_id, clients_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    $stmt->execute([$firstName, $lastName, $email, $telephone, $birthday, $lieuNaissance, $numCNI, $fileContent, $adresse, $city, $cp, intval($_SESSION['user']["agence_id"]), $typeCustomerValue]);
                    
                    echo '
                        <div class="pop_up">
                            <div class="pop_content">
                                <h1>Le client à bien été créer</h1>
                                <p>Voulez-vous créer un nouveau véhicule ?</p>

                                <div class="input_btn">
                                    <a href="createVehicle.php" class="btn yes">Oui</a>
                                    <a href="../../index.php" class="btn no">Non</a>
                                </div>
                            </div>
                        </div>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                document.getElementById("body").style.overflow = "hidden";
                            });
                        </script>

                        ';
                } else {
                    echo "Invalid file type. Only PNG, JPEG, and JPG are allowed.";
                }
            } else {
                echo "Error uploading file.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets//css/pop_up.css">

    <title>Myseven - Créer un client</title>
</head>

<body id="body">
    <main>

        <div class="search-container">
            <h2>Créer un client</h2>
            <form class="gap" id="form_pdf" method="POST" enctype="multipart/form-data">
                <div class="input_box">
                    <span class="label form_required">Nom de famille</span>
                    <input required name="firstName" type="text" id="firstName">
                </div>

                <div class="input_box">
                    <span class="label form_required">Prénom</span>
                    <input required name="lastName" type="text" id="lastName">
                </div>

                <div class="input_box">
                    <span class="label form_required">Adresse mail</span>
                    <input required name="email" type="email" id="email">
                </div>

                <div class="input_box">
                    <span class="label form_required">Numéro de téléphone</span>
                    <input required name="telephone" min="0" type="number" id="telephone">
                </div>

                <div class="input_box">
                    <span class="label form_required">Date de naissance</span>
                    <input required name="birthday" max="<?php echo date('Y-m-d'); ?>" type="date" id="birthday">
                </div>

                <div class="input_box">
                    <span class="label form_required">Lieu de naissance</span>
                    <input required name="lieuNaissance" type="text" id="lieuNaissance">
                </div>

                <div class="input_box">
                    <span class="label form_required">Numéro CNI</span>
                    <input required type="number" id="numCNI" name="numCNI">
                </div>

                <div class="input_box">
                    <span class="label form_required">Ajouter la CNI (png, jpg, jpeg, pdf)</span>
                    <input required type="file" id="fileCNI" name="fileCNI">
                </div>

                <div class="input_box">
                    <span class="label form_required">Adresse</span>
                    <input required type="text" id="adresse" name="adresse">
                </div>

                <div class="input_box">
                    <span class="label form_required">Ville</span>
                    <input required type="text" id="city" name="city">
                </div>

                <div class="input_box">
                    <span class="label form_required">Code Postal</span>
                    <input required type="number" id="cp" name="cp">
                </div>

                <div class="input_box">
                    <span class="label form_required">Type de client</span>
                    <select required name="typeCustomer">
                        <option value=0>-- Choisir le type de client --</option>
                        <option value=1>Acheteur</option>
                        <option value=2>Vendeur</option>
                    </select>
                </div>

                <div class="input_box">
                    <input class="submit_btn" value="Créer le client" type="submit" name="submit_btn" id="submit_btn">
                </div>
            </form>
        </div>
    </main>
</body>

</html>