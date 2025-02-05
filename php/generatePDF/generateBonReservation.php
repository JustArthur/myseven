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
        $resClient['adresse'],
        $resClient['cp'],
        $resClient['ville'],
        $resClient['telephone'],
        $resClient['email'],
        $resVehicule['marque'] . ' ' . $resVehicule['model'],
        $resVehicule['immatriculation'],
        //Prix véhicule seul
        //Frais mise en route
        //Garantie mécanique
        //Livraison
        $resVehicule['marque'] . ' ' . $resVehicule['model'],
        $resVehicule['immatriculation'],
        $resVehicule['kilometrage'],
        //MEC
        //Prix de reprise
    ];

    $pdfNameFile = "BON DE RESERVATION 2023 CAMBRAI " . $importVarPDF[0] . ".pdf";

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../pdf/BON_DE_RESERVATION_2023_CAMBRAI.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importCoordinates = [
        ['x' => 103, 'y' => 51], //nom prénom
        ['x' => 93, 'y' => 58], //adresse
        ['x' => 98, 'y' => 65], //cp
        ['x' => 130, 'y' => 65], //ville
        ['x' => 85, 'y' => 72], //telephone
        ['x' => 130, 'y' => 72], //email
        ['x' => 42, 'y' => 97], //marque model
        ['x' => 42, 'y' => 103], //immat
        //Prix véhicule seul
        //Frais mise en route
        //Garantie mécanique
        //Livraison
        ['x' => 143, 'y' => 191], //marque model
        ['x' => 140, 'y' => 196], //immat
        ['x' => 136, 'y' => 201], //kilometrage
        //MEC
        //Prix de reprise
    ];

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../PDF_saved/BonReservation/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
    $DBB->closeConnection();
?>