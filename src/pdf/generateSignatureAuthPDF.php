<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(empty($_SESSION['user']) || empty($_COOKIE['user_session'])) {
        header('Location: ../../login.php');
        exit();
    } else if (empty($_POST['client']) || empty($_POST['immatCar'])) {
        header('Location: ../../index.php');
        exit();
    }

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../functions/createFolderNextCloud.php';
    require_once '../../database.php';
    require_once '../functions/cleanValues.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_email = ?');
    $resClient->execute([$_POST['client']]);
    $resClient = $resClient->fetch();

    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute([$_SESSION['user']['agence_id']]);
    $resAgence = $resAgence->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $dateParts = explode('-', $resClient['clients_anniversaire']);
    $year = $dateParts[0];
    $month = $dateParts[1];
    $day = $dateParts[2];


    $importVarPDF = [
        strtoupper($resClient['clients_nom']) . ' ' . $resClient['clients_prenom'],
        $day,
        $month,
        $year,
        $resClient['clients_lieu_naissance'],
        $resClient['clients_rue'] . ' ' . ucfirst($resClient['clients_ville']) . ' ' . $resClient['clients_cp'],
        $resAgence['agence_nom'],
        $resVehicule['vehicules_marque'] . ' ' . $resVehicule['vehicules_model'],
        $resVehicule['vehicules_immatriculation'],
        $resAgence['agence_nom'],
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
        ['x' => 47, 'y' => 96],  // mois anniv
        ['x' => 55, 'y' => 96],  // annee anniv
        ['x' => 72, 'y' => 96],  // lieu naissance
        ['x' => 48, 'y' => 104],  // adresse
        ['x' => 82, 'y' => 121],  // agence
        ['x' => 58, 'y' => 147],  // marque model
        ['x' => 50, 'y' => 155],  // immat
        ['x' => 27, 'y' => 199],  // agence
        ['x' => 65, 'y' => 199],  // day
        ['x' => 74, 'y' => 199],  // month
        ['x' => 82, 'y' => 199]  // year
    ];

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(11);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../storage/signature_auth/";

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

    $pattern = $folder . "PROCURATION_DE_SIGNATURE_" . $cleanedValueName . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "PROCURATION_DE_SIGNATURE_" . $cleanedValueName . "_" . $fileCount . ".pdf";
    $destinationPath = $folder . $pdfNameFile;

    $getAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $getAgence->execute([intval($_SESSION['user']["agence_id"])]);
    $getAgence = $getAgence->fetch();
    $DBB->closeConnection();

    $pdf->Output('F', $destinationPath);    
    $t= uploadPdfToNextcloud($getAgence['agence_path_client'], $cleanedValueName, $destinationPath);
    $y= uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $cleanedValueVehicule, $destinationPath);
    $pdf->Output('I', $pdfNameFile);
?>