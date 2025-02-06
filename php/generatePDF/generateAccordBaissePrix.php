<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../php/login.php');
        exit();
    }

    require_once('../../vendor/setasign/fpdf/fpdf.php');
    require_once('../../vendor/setasign/fpdi/src/autoload.php');

    require_once("../../connexionDB.php");

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();

    $prixNetVendeur = 1000;
    
    $resClient = $DB->prepare('SELECT * FROM clients WHERE email = ?');
    $resClient->execute([$_POST['client']]);
    $resClient = $resClient->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $day = date("d");
    $month = date("m");
    $year = date("Y");

    $importVarPDF = [
        $resClient['nom'] . ' ' . $resClient['prenom'],
        $resVehicule['marque'] . ' ' . $resVehicule['model'], //marque model
        $resVehicule['immatriculation'], //immat
        $prixNetVendeur,
        $day,
        $month,
        $year
    ];

    $pdfNameFile = "ACCORD DE BAISSE DU PRIX NET VENDEUR " . $importVarPDF[0] . " " . random_int(0, 9999) . ".pdf";

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../pdf/ACCORD_DE_BAISSE_DU_PRIX_NET_VENDEUR.pdf');
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
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../PDF_saved/AccordBaissePrix/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
    $DBB->closeConnection();
?>