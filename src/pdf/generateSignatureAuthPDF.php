<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    }

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../database.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();


    $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_email = ?');
    $resClient->execute([$_POST['client']]);
    $resClient = $resClient->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $importVarPDF = [
        strtoupper($resClient['clients_nom'] . ' ' . $resClient['clients_prenom']),
        //date anniv
        //lieu naissance
        strtoupper($resClient['clients_rue'] . ' ' . ucfirst($resClient['clients_ville']) . ' ' . $resClient['clients_cp']),
        strtoupper($resVehicule['vehicules_marque'] . ' ' . $resVehicule['vehicules_model']),
        strtoupper($resVehicule['vehicules_immatriculation']),
        strtoupper(date("d")),
        strtoupper(date("m")),
        strtoupper(date("Y"))
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/signature_authorization.pdf');
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

    $folder = "../../storage/signature_auth/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $pattern = $folder . "PROCURATION_DE_SIGNATURE_" . preg_quote($importVarPDF[0], '/') . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "PROCURATION_DE_SIGNATURE_" . $importVarPDF[0] . "_" . $fileCount . ".pdf";

    $DBB->closeConnection();
    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
?>