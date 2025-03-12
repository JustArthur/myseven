<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    } else if (empty($_POST['client']) || empty($_POST['immatCar'])) {
        header('Location: ../../index.php');
        exit();
    }

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../database.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $prixNetVendeur = 1000; /// ATTENTION A CHANGER
    
    $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_email = ?');
    $resClient->execute([$_POST['client']]);
    $resClient = $resClient->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $importVarPDF = [
        $resClient['clients_nom'] . ' ' . $resClient['clients_prenom'],
        $resVehicule['vehicules_marque'] . ' ' . $resVehicule['vehicules_model'],
        $resVehicule['vehicules_immatriculation'],
        $prixNetVendeur,
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
        ['x' => 55, 'y' => 161],  // day
        ['x' => 68, 'y' => 161],  // month
        ['x' => 80, 'y' => 161]  // year
    ];

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../storage/price_reduction/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $pattern = $folder . "ACCORD_DE_BAISSE_DU_PRIX_NET_VENDEUR_" . $importVarPDF[0] . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "ACCORD_DE_BAISSE_DU_PRIX_NET_VENDEUR_" . $importVarPDF[0] . "_" . $fileCount . ".pdf";

    $DBB->closeConnection();
    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
?>