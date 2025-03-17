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


    $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_email = ?');
    $resClient->execute([$_POST['client']]);
    $resClient = $resClient->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $dateParts = explode('-', $resClient['clients_anniversaire']);
    $year = $dateParts[0];
    $month = $dateParts[1];
    $day = $dateParts[2];


    $importVarPDF = [
        $resClient['clients_nom'] . ' ' . $resClient['clients_prenom'],
        $day,
        $month,
        $year,
        $resClient['clients_lieu_naissance'],
        $resClient['clients_rue'] . ' ' . ucfirst($resClient['clients_ville']) . ' ' . $resClient['clients_cp'],
        $resVehicule['vehicules_marque'] . ' ' . $resVehicule['vehicules_model'],
        $resVehicule['vehicules_immatriculation'],
        date("d"),
        date("m"),
        date("Y")
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/signature_authorization.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importCoordinates = [
        ['x' => 52, 'y' => 87],  // nom prénom
        ['x' => 39, 'y' => 96],  // jour anniv
        ['x' => 47, 'y' => 96],  // jour mois
        ['x' => 55, 'y' => 96],  // jour annee
        ['x' => 72, 'y' => 96],  // lieu naissance
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
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../storage/signature_auth/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $pattern = $folder . "PROCURATION_DE_SIGNATURE_" . $importVarPDF[0] . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "PROCURATION_DE_SIGNATURE_" . $importVarPDF[0] . "_" . $fileCount . ".pdf";

    $DBB->closeConnection();
    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
?>