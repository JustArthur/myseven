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


    $resClient = $DB->prepare(query: 'SELECT * FROM Clients WHERE email = ?');
    $resClient->execute(params: [$_POST['client']]);
    $resClient = $resClient->fetch();

    $resVehicule = $DB->prepare(query: 'SELECT * FROM vehicules WHERE immatriculation = ?');
    $resVehicule->execute(params: [$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $importVarPDF = [
        $resClient['nom'] . ' ' . $resClient['prenom'],
        //date anniv
        //lieu naissance
        $resClient['adresse'] . ' ' . ucfirst(string: $resClient['ville']) . ' ' . $resClient['cp'],
        $resVehicule['marque'] . ' ' . $resVehicule['model'],
        $resVehicule['immatriculation'],
        date(format: "d"),
        date(format: "m"),
        date(format: "Y")
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile(file: '../../pdf/PROCURATION_DE_SIGNATURE.pdf');
    $pageId = $pdf->importPage(pageNumber: 1, box: \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage(pageId: $pageId, x: 5, y: 10, width: 200);

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
        $pdf->SetFont(family: 'Helvetica');
        $pdf->SetTextColor(r: 0, g: 0, b: 0);
        $pdf->SetXY(x: $importCoordinates[$index]['x'], y: $importCoordinates[$index]['y']);
        $pdf->Write(h: 0, txt: $valPDF);
    }

    $folder = "../../PDF_saved/ProcurationSignature/";

    if(!file_exists(filename: $folder)) {
        mkdir(directory: $folder, permissions: 0777, recursive: true);
    }

    $fileCount = count(value: glob(pattern: $folder . "*.pdf")) + 1;
    $pdfNameFile = "PROCURATION_DE_SIGNATURE_" . $importVarPDF[0] . "_" . $fileCount . ".pdf";

    $DBB->closeConnection();
    $pdf->Output(dest: 'I', name: $pdfNameFile);
    $pdf->Output(dest: 'F', name: $folder . $pdfNameFile);
?>