<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(empty($_SESSION['user']) || empty($_COOKIE['user_session'])) {
        header('Location: ../../login.php');
        exit();
    } 

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../database.php';
    require_once '../functions/createFolderNextCloud.php';
    require_once '../functions/cleanValues.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();
    
    if (!empty($_POST['clientEmail'])) {
        $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_email = ?');
        $resClient->execute([$_POST['clientEmail']]);
        $resClient = $resClient->fetch();

    } else if (!empty($_POST['idClient'])){
        $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_id = ?');
        $resClient->execute([$_POST['idClient']]);
        $resClient = $resClient->fetch();
        
    } else {
        header('Location: ../../index.php');
        exit();
    }

    if(!empty($_POST['typeVehicle']) && !empty($_POST['numSerie'] && !empty($_POST['idVehicule']))) {
        $updateVehicules = $DB->prepare("UPDATE vehicules SET vehicules_type = ?, vehicules_numero_serie = ? WHERE vehicules_id = ?");
        $updateVehicules->execute([$_POST['typeVehicle'], $_POST['numSerie'], $_POST['idVehicule']]);
    }

    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute([$_SESSION['user']['agence_id']]);
    $resAgence = $resAgence->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    if(empty($resVehicule['vehicules_type']) || empty($resVehicule['vehicules_numero_serie'])) {
        header('Location: ../forms/tempFileInformationSell.php?idClient=' . $resClient['clients_id'] . '&idVehicule=' . $resVehicule['vehicules_id'] . '');
        exit();
    }

    $importVarPDF = [
        $resClient['clients_nom'] . ' ' . $resClient['clients_prenom'],
        $resClient['clients_rue'] . ' ' . $resClient['clients_ville'] . ' ' . $resClient['clients_cp'],
        $resClient['clients_numero_cni'],
        $resVehicule['vehicules_marque'],
        $resVehicule['vehicules_model'],
        $resVehicule['vehicules_type'],
        $resVehicule['vehicules_immatriculation'],
        $resVehicule['vehicules_kilometrage'],
        $resVehicule['vehicules_couleur'],
        $resVehicule['vehicules_puissance'],
        $resVehicule['vehicules_numero_serie'],
        date('d/m/Y', strtotime($resVehicule['vehicules_date_mise_en_circu'])),
        date('d/m/Y', strtotime($resVehicule['vehicules_date_entretien'])),
        // PV N° ,
        $resAgence['agence_nom'],
        date('d/m/Y')
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/information_sell.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importCoordinates = [
        ['x' => 55, 'y' => 71.5], // Nom et prénom Client
        ['x' => 55, 'y' => 76], // Adresse Client
        ['x' => 63, 'y' => 81], // Numéro CNI
        ['x' => 45, 'y' => 101], // Marque véhicule
        ['x' => 45, 'y' => 105.5], // Modèle véhicule
        ['x' => 45, 'y' => 110], // Type
        ['x' => 58, 'y' => 115], // Immatriculation véhicule
        ['x' => 51, 'y' => 120], // Kilomètrage véhicule
        ['x' => 135, 'y' => 100.5], // Couleur véhicule
        ['x' => 150, 'y' => 105.5], // Puissance véhicule
        ['x' => 140, 'y' => 110], // Numéro de série
        ['x' => 140, 'y' => 115], // Mise en circulation véhicule
        ['x' => 125, 'y' => 159.5], // Date dernier contrôle technique
        // ['x' => 155, 'y' => 159.5], // PV N°
        ['x' => 52, 'y' => 219.5], // Nom de l'agence
        ['x' => 88, 'y' => 219.5] // Date du jour
    ];

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(11);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../storage/information_sell/";

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

    $pattern = $folder . "INFORMATION_RELATIVE_VENTE_" . $cleanedValueName . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "INFORMATION_RELATIVE_VENTE_" . $cleanedValueName . "_" . $fileCount . ".pdf";
    $destinationPath = $folder . $pdfNameFile;

    $getAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $getAgence->execute([intval($_SESSION['user']["agence_id"])]);
    $getAgence = $getAgence->fetch();
    $DBB->closeConnection();

    $pdf->Output('F', $destinationPath);
    // uploadPdfToNextcloud($getAgence['agence_path_client'], $cleanedValueName, $destinationPath);
    uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $cleanedValueVehicule, $destinationPath);
    $pdf->Output('I', $pdfNameFile);
?>