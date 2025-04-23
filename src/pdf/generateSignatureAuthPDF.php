<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../functions/createFolderNextCloud.php';
    require_once '../../database.php';
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

    if (!empty($_POST['clientEmail'])) {
        $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_email = ?');
        $resClient->execute([$_POST['clientEmail']]);
        $resClient = $resClient->fetch();

    } else if (!empty($_POST['idClient'])){
        $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_id = ?');
        $resClient->execute([$_POST['idClient']]);
        $resClient = $resClient->fetch();
        
    } else {
        echo '
            <script>
                alert("Impossible de trouver le client.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }


    if(!$resClient || empty($_POST['immatCar'])) {
        echo '
            <script>
                alert("Impossible de trouver le client ou la plaque d\'immatriculation est invalide.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute([$_SESSION['user']['agence_id']]);
    $resAgence = $resAgence->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $dateParts = explode('-', $resClient['clients_anniversaire']);
    $year = $dateParts[0];
    $month = $dateParts[1];
    $day = $dateParts[2];


    $importVarPDF = [
        strtoupper($resClient['clients_nom']) . ' ' . $resClient['clients_prenom'],
        $day,
        $month,
        $year,
        $resClient['clients_lieu_naissance'],
        $resClient['clients_rue'] . ' ' . ucfirst($resClient['clients_ville']) . ' ' . $resClient['clients_cp'],
        $resAgence['agence_nom'],
        $resVehicule['vehicules_marque'] . ' ' . $resVehicule['vehicules_model'],
        $resVehicule['vehicules_immatriculation'],
        $resAgence['agence_nom'],
        date("d"),
        date("m"),
        date("Y")
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/signature_authorization.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importPDFData = [
        [ 'value' => strtoupper($resClient['clients_nom']) . ' ' . $resClient['clients_prenom'], 'x' => 52, 'y' => 87 ],
        [ 'value' => $day, 'x' => 39, 'y' => 96 ],
        [ 'value' => $month, 'x' => 47, 'y' => 96 ],
        [ 'value' => $year, 'x' => 55, 'y' => 96 ],
        [ 'value' => $resClient['clients_lieu_naissance'], 'x' => 72, 'y' => 96 ],
        [ 'value' => $resClient['clients_rue'] . ' ' . ucfirst($resClient['clients_ville']) . ' ' . $resClient['clients_cp'], 'x' => 48, 'y' => 104 ],
        [ 'value' => $resAgence['agence_nom'], 'x' => 82, 'y' => 121 ],
        [ 'value' => $resVehicule['vehicules_marque'] . ' ' . $resVehicule['vehicules_model'], 'x' => 58, 'y' => 147 ],
        [ 'value' => $resVehicule['vehicules_immatriculation'], 'x' => 50, 'y' => 155 ],
        [ 'value' => $resAgence['agence_nom'], 'x' => 27, 'y' => 199 ],
        [ 'value' => date("d"), 'x' => 65, 'y' => 199 ],
        [ 'value' => date("m"), 'x' => 74, 'y' => 199 ],
        [ 'value' => date("Y"), 'x' => 82, 'y' => 199 ]
    ];

    foreach ($importPDFData as $data) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(11);
        $pdf->SetXY($data['x'], $data['y']);
        $value = mb_convert_encoding($data['value'], 'windows-1252', 'UTF-8');
        $pdf->Write(0, $value);
    }

    $folder = "../../storage/signature_auth/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $cleanBrand = cleanValue($resVehicule['vehicules_marque']);
    $cleanModel = cleanValue($resVehicule['vehicules_model']);
    $cleanImmatriculation = cleanValue($resVehicule['vehicules_immatriculation']);
    $cleanNom = cleanValue($resClient['clients_nom']);
    $cleanPrenom = cleanValue($resClient['clients_prenom']);

    $cleanedValueName = $cleanNom . '-' . $cleanPrenom;
    $cleanedValueVehicule = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/DOCUMENTS_DE_VENTE/CLIENT_VENDEUR/';

    $pattern = $folder . "PROCURATION_DE_SIGNATURE_" . $cleanedValueName . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "PROCURATION_DE_SIGNATURE_" . $cleanedValueName . "_" . $fileCount . ".pdf";
    $destinationPath = $folder . $pdfNameFile;

    $getAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $getAgence->execute([intval($_SESSION['user']["agence_id"])]);
    $getAgence = $getAgence->fetch();
    $DBB->closeConnection();

    $pdf->Output('F', $destinationPath);    
    // uploadPdfToNextcloud($getAgence['agence_path_client'], $cleanedValueName, $destinationPath);
    uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $cleanedValueVehicule, $destinationPath);
    $pdf->Output('I', $pdfNameFile);
?>