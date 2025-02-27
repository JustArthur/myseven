<?php
    ini_set(option: 'display_errors', value: '1');
    ini_set(option: 'display_startup_errors', value: '1');
    error_reporting(error_level: E_ALL);

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header(header: 'Location: ../../php/login.php');
        exit();
    }

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../connexionDB.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();

    $prixNetVendeur = 1000;
    
    $resClient = $DB->prepare(query: 'SELECT * FROM Clients WHERE email = ?');
    $resClient->execute(params: [$_POST['client']]);
    $resClient = $resClient->fetch();

    $resVehicule = $DB->prepare(query: 'SELECT * FROM vehicules WHERE immatriculation = ?');
    $resVehicule->execute(params: [$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $day = date(format: "d");
    $month = date(format: "m");
    $year = date(format: "Y");

    $importVarPDF = [
        $resClient['nom'] . ' ' . $resClient['prenom'],
        $resVehicule['marque'] . ' ' . $resVehicule['model'],
        $resVehicule['immatriculation'],
        $prixNetVendeur,
        $day,
        $month,
        $year
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile(file: '../../pdf/ACCORD_DE_BAISSE_DU_PRIX_NET_VENDEUR.pdf');
    $pageId = $pdf->importPage(pageNumber: 1, box: \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage(pageId: $pageId, x: 5, y: 10, width: 200);

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
        $pdf->SetFont(family: 'Helvetica');
        $pdf->SetTextColor(r: 0, g: 0, b: 0);
        $pdf->SetXY(x: $importCoordinates[$index]['x'], y: $importCoordinates[$index]['y']);
        $pdf->Write(h: 0, txt: $valPDF);
    }

    $folder = "../../PDF_saved/AccordBaissePrix/";

    if(!file_exists(filename: $folder)) {
        mkdir(directory: $folder, permissions: 0777, recursive: true);
    }

    $fileCount = count(value: glob(pattern: $folder . "*.pdf")) + 1;
    $pdfNameFile = "ACCORD_DE BAISSE_DU_PRIX_NET_VENDEUR_" . $importVarPDF[0] . "_" . $fileCount . ".pdf";

    $DBB->closeConnection();
    $pdf->Output(dest: 'I', name: $pdfNameFile);
    $pdf->Output(dest: 'F', name: $folder . $pdfNameFile);
?>