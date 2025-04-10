<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../database.php';
    require_once '../functions/createFolderNextCloud.php';
    require_once '../functions/cleanValues.php';

    if(empty($_SESSION['user']) || empty($_COOKIE['user_session'])) {
        header('Location: ../../login.php');
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
        header('Location: ../../login.php');
        exit();
    }

    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute([$_SESSION['user']['agence_id']]);
    $resAgence = $resAgence->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $importVarPDF = [
        strtoupper($resClient['clients_nom']) . ' ' . $resClient['clients_prenom'],
        $resVehicule['vehicules_marque'] . ' ' . $resVehicule['vehicules_model'],
        $resVehicule['vehicules_immatriculation'],
        $_POST['netVendeur'],
        $resAgence['agence_nom'],
        $resAgence['agence_nom'],
        date("d"),
        date("m"),
        date("Y")
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/price_reduction_agreement.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importCoordinates = [
        ['x' => 52, 'y' => 91],  // nom prénom
        ['x' => 70, 'y' => 102],  // marque model
        ['x' => 45, 'y' => 112],  // immat
        ['x' => 118, 'y' => 123],  // prix net vendeur
        ['x' => 83, 'y' => 134],  // agence
        ['x' => 23, 'y' => 161],  // agence
        ['x' => 68, 'y' => 161],  // day
        ['x' => 78.5, 'y' => 161],  // month
        ['x' => 91, 'y' => 161]  // year
    ];

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(11);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../storage/price_reduction/";

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

    $pattern = $folder . "ACCORD_DE_BAISSE_DU_PRIX_NET_VENDEUR_" . $cleanedValueName . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "ACCORD_DE_BAISSE_DU_PRIX_NET_VENDEUR_" . $cleanedValueName . "_" . $fileCount . ".pdf";
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