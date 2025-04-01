<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    } else if (empty($_POST['customerMail']) || empty($_POST['immatCar'])) {
        header('Location: ../../index.php');
        exit();
    }

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../database.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();
    
    $resClient = $DB->prepare('SELECT * FROM clients INNER JOIN agence ON agence.agence_id = clients.clients_agence_id WHERE clients.clients_email = ?');
    $resClient->execute([$_POST['customerMail']]);
    $resClient = $resClient->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $importVarPDF = [
        $resClient['clients_nom'] . ' ' . $resClient['clients_prenom'],
        $resVehicule['vehicules_marque'] . ' ' . $resVehicule['vehicules_model'],
        $resVehicule['vehicules_immatriculation'],
        $resClient['agence_nom'],
        $_POST['netVendeur'],
        $resClient['agence_nom'],
        date('d/m/Y')
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/contract_engagement.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importCoordinates = [
        ['x' => 52, 'y' => 167], //Nom prénom
        ['x' => 140, 'y' => 167], //Marque Model
        ['x' => 50, 'y' => 176], //Immatriculation
        ['x' => 30, 'y' => 185], //Agence
        ['x' => 123, 'y' => 185], //Montant Net
        ['x' => 118, 'y' => 253], //Agence
        ['x' => 158, 'y' => 253], //Date
    ];

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../storage/contract_engagement/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $pattern = $folder . "MANDAT_ENGAGEMENT_" . $importVarPDF[0] . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "MANDAT_ENGAGEMENT_" . $importVarPDF[0] . "_" . $fileCount . ".pdf";

    $DBB->closeConnection();
    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
?>