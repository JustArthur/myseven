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
        //date anniv
        //lieu naissance
        $resClient['adresse'] . ' ' . ucfirst($resClient['ville']) . ' ' . $resClient['cp'],
        $resVehicule['marque'] . ' ' . $resVehicule['model'],
        $resVehicule['immatriculation'], //immat
        $day,
        $month,
        $year
    ];

    $pdfNameFile = "PROCURATION DE SIGNATURE " . $importVarPDF[0] . " " . random_int(0, 9999) . ".pdf";

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../pdf/PROCURATION_DE_SIGNATURE.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importCoordinates = [
        ['x' => 52, 'y' => 87],  // nom prénom
        //date anniv
        //lieu naissance
        ['x' => 48, 'y' => 104],  // adresse
        ['x' => 58, 'y' => 147],  // marque model
        ['x' => 50, 'y' => 155],  // immat
        ['x' => 51, 'y' => 199],  // day
        ['x' => 59, 'y' => 199],  // month
        ['x' => 67, 'y' => 199]  // year
    ];

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../PDF_saved/ProcurationSignature/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
    $DBB->closeConnection();
?>