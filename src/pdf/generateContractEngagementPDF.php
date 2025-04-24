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

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../database.php';
    require_once '../functions/createFolderNextCloud.php';
    require_once '../functions/cleanValues.php';

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
                alert("Impossible de trouver le client ou la plaque d\'immatriculation.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    } else if ($resClient['clients_type'] != 'Vendeur') {
        echo '
            <script>
                alert("Attention ce client n\'est pas un vendeur.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }

    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute([$_SESSION['user']['agence_id']]);
    $resAgence = $resAgence->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/contract_engagement.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importPDFData = [
        [ 'value' => strtoupper($resClient['clients_nom']) . ' ' . $resClient['clients_prenom'], 'x' => 51, 'y' => 103 ],
        [ 'value' => $resVehicule['vehicules_marque'] . ' ' . $resVehicule['vehicules_model'], 'x' => 135, 'y' => 103 ],
        [ 'value' => $resVehicule['vehicules_immatriculation'], 'x' => 48, 'y' => 109 ],
        [ 'value' => $resAgence['agence_nom'], 'x' => 56, 'y' => 115.5 ],
        [ 'value' => $_POST['netVendeur'], 'x' => 147, 'y' => 115.5 ],
        [ 'value' => $resAgence['agence_nom'], 'x' => 75, 'y' => 157.5 ],
        [ 'value' => date('d/m/Y'), 'x' => 115, 'y' => 157.5 ]
    ];

    foreach ($importPDFData as $data) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(11);
        $pdf->SetXY($data['x'], $data['y']);
        $value = mb_convert_encoding($data['value'], 'windows-1252', 'UTF-8');
        $pdf->Write(0, $value);
    }

    $cleanBrand = cleanValue($resVehicule['vehicules_marque']);
    $cleanModel = cleanValue($resVehicule['vehicules_model']);
    $cleanImmatriculation = cleanValue($resVehicule['vehicules_immatriculation']);
    $cleanNom = cleanValue($resClient['clients_nom']);
    $cleanPrenom = cleanValue($resClient['clients_prenom']);

    $folder = "../../storage/" . $cleanNom . "-" . $cleanPrenom . "/mandat_engagement/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $cleanedValueName = $cleanNom . '-' . $cleanPrenom;
    $cleanedValueVehicule = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/DOCUMENTS_DE_VENTE/CLIENT_VENDEUR/';

    $pattern = $folder . "MANDAT_ENGAGEMENT_" . $cleanedValueName . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "MANDAT_ENGAGEMENT_" . $cleanedValueName . "_" . $fileCount . ".pdf";
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