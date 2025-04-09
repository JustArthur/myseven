<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

if (empty($_SESSION['user']) || empty($_COOKIE['user_session'])) {
    header('Location: ../../login.php');
    exit();
}

if (!empty($_POST)) {
    extract(array: $_POST);
    if (isset($_POST['submit_btn'])) {

        if (empty($immatCar)) {
            $_GET['client_email'] = $client;
            $error_message = [
                'type' => 'error',
                'message' => 'Aucune immatriculation selectionné.'
            ];
        } else {

            require_once '../../database.php';
            require_once '../functions/createFolderNextCloud.php';

            $DBB = new ConnexionDB;
            $DB = $DBB->openConnection();

            $client_email = $_GET['client_email'];
            $stmt = $DB->prepare("SELECT * FROM clients WHERE clients_email = ?");
            $stmt->execute([urldecode($_GET['client_email'])]);
            $resClient = $stmt->fetch();

            $resVehicule = $DB->prepare("SELECT * FROM vehicules WHERE vehicules_immatriculation = ?");
            $resVehicule->execute([$immatCar]);
            $resVehicule = $resVehicule->fetch();

            if ($resClient['clients_copie_cni']) {
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

                $cleanBrand = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($resVehicule['vehicules_marque']));
                $cleanModel = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($resVehicule['vehicules_model']));
                $cleanImmatriculation = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($resVehicule['vehicules_immatriculation']));
                $cleanNom = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($resClient['clients_nom']));
                $cleanPrenom = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($resClient['clients_prenom']));

                $tempFilePath = sys_get_temp_dir() . "/CNI_" . $cleanNom . "_" . $cleanPrenom . ".jpg";
                file_put_contents($tempFilePath, $fileContent);

                $getAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
                $getAgence->execute([intval($_SESSION['user']["agence_id"])]);
                $getAgence = $getAgence->fetch();


                $CNItoUpload = $cleanBrand . '/' . $cleanModel . '_' . $cleanImmatriculation . '/' . "DOCUMENTS_DE_VENTE/CLIENT_ACHETEUR/";

                $uploadSuccess = uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $CNItoUpload, $tempFilePath);

                unlink($tempFilePath);

                echo '
                    <form id="redirectForm" action="reservationForm.php" method="POST">
                        <input type="hidden" name="client" value="' . strtolower($_GET['client_email']) . '">
                        <input type="hidden" name="immatCar" value="' . $immatCar . '">
                    </form>
                    <script>
                        document.getElementById("redirectForm").submit();
                    </script>
                ';
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

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <link rel="stylesheet" href="../../assets/css/forms.css">

    <title>Myseven - Selectionner un véhicule</title>
</head>

<body id="body">
    <main>

        <div class="search-container">
            <h2>Selectionner un véhicule</h2>
            <form id="form_pdf" method="POST">
                <?php if (!empty($error_message)) {
                    echo "<div style='margin-bottom: 30px;' class='error_message " . $error_message['type'] . "'>" . $error_message['message'] . "</div>";
                } ?>

                <input type="hidden" name="client" value="<?= $_GET['client_email'] ?>">

                <div class="wrapper marque">
                    <span class="label form_required">Immatriculation du véhicule</span>
                    <div class="select-btn">
                        <span class="select">Selectionner une immatriculation...</span>
                        <i class="uil uil-angle-down"></i>
                    </div>
                    <div class="content">
                        <div class="search">
                            <input type="text" placeholder="Rechercher une immatriculation...">
                        </div>
                        <ul id="carSelect" class="options"></ul>
                    </div>
                </div>

                <input type="hidden" name="immatCar" id="marque_id">

                <div class="input_box">
                    <input class="submit_btn" type="submit" name="submit_btn" id="submit_btn" value="Selectionner ce véhicule">
                </div>
            </form>
        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
    <script src="../../assets/js/wrapperSelectImmatriculation.js"></script>
</body>

</html>