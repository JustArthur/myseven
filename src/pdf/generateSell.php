<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../database.php';
    require_once '../functions/createFolderNextCloud.php';
    require_once '../functions/cleanValues.php';

    if(empty($_SESSION['user']) || empty($_COOKIE['user_session'])) {
        header('Location: ../../login.php');
        exit();
    }

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $resClientAcheteur = $DB->prepare('SELECT * FROM clients WHERE clients_id = ?');
    $resClientAcheteur->execute([$_POST['idClientAcheteur']]);
    $resClientAcheteur = $resClientAcheteur->fetch();

    $resClientVendeur = $DB->prepare('SELECT * FROM clients WHERE clients_id = ?');
    $resClientVendeur->execute([$_POST['idClientVendeur']]);
    $resClientVendeur = $resClientVendeur->fetch();

    if(!$resClientAcheteur || !$resClientVendeur || empty($_POST['immatCar'])) {
        header('Location: ../../index.php');
        exit();
    }

    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute([$_SESSION['user']['agence_id']]);
    $resAgence = $resAgence->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $importVarPDF = [
        $resVehicule['vehicules_marque'],
        $resVehicule['vehicules_model'],
        date('d/m/Y', strtotime($resVehicule['vehicules_date_mise_en_circu'])),
        $resVehicule['vehicules_couleur'],
        $resVehicule['vehicules_immatriculation'],
        $resVehicule['vehicules_kilometrage'],
        $resClientVendeur['clients_nom'] . ' ' . $resClientVendeur['clients_telephone'],
        $_POST['notesClientAcheteur'],
        $resClientAcheteur['clients_nom'] . ' ' . $resClientAcheteur['clients_telephone'],
        $_POST['notesClientVendeur'],
        //Carte grise titulaire
        //Carte grise co-titulaire
        date('d/m/Y', strtotime($_POST['dateCashSentinel'])),
        date('d/m/Y', strtotime($_POST['dateLivraisonPossible'])),
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/sell.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $crossToInsert = [];

    switch($_POST['garantie']) {
        case 'allRisk':
            $crossToInsert[] = ['x' => 42, 'y' => 223.5];
            break;

        case 'compelete':
            $crossToInsert[] = ['x' => 66.5, 'y' => 223.5];
            break;

        case 'essentiel':
            $crossToInsert[] = ['x' => 87, 'y' => 223.5];
            break;

        case 'mbp':
            $crossToInsert[] = ['x' => 108.5, 'y' => 223.5];
            break;
    }

    switch($_POST['askGarantieConstructeur']) {
        case 'yes':
            $crossToInsert[] = ['x' => 42, 'y' => 228];
            break;

        case 'no':
            $_POST['dureeGarantieConstructeur'] = 'none';
    }

    switch($_POST['dureeGarantieConstructeur']) {
        case '3mois':
            $crossToInsert[] = ['x' => 108.5, 'y' => 228];
            break;

        case '6mois':
            $crossToInsert[] = ['x' => 124.5, 'y' => 228];
            break;

        case '12mois':
            $crossToInsert[] = ['x' => 140, 'y' => 228];
            break;

        case '24mois':
            $crossToInsert[] = ['x' => 157.5, 'y' => 228];
            break;
    }

    switch($_POST['typePaiement']) {
        case 'arrhes':
            $_POST['avanceInter'] = 'none';
            break;
        
        case 'avanceInter':
            $_POST['arrhes'] = 'none';
            break;
    }

    switch($_POST['arrhes']) {
        case 'CB':
            $crossToInsert[] = ['x' => 149, 'y' => 244];
            break;

        case 'cheque':
            $crossToInsert[] = ['x' => 169, 'y' => 244];
            break;
    }

    switch($_POST['avanceInter']) {
        case 'virement':
            $crossToInsert[] = ['x' => 141, 'y' => 253.5];
            break;

        case 'cash':
            $crossToInsert[] = ['x' => 158.5, 'y' => 253.5];
            break;
    }

    $importCoordinates = [
        ['x' => 45, 'y' => 55], // Marque
        ['x' => 45, 'y' => 60], // Modèle
        ['x' => 45, 'y' => 65], // Date de mise en circulation
        ['x' => 135, 'y' => 55], // Couleur
        ['x' => 135, 'y' => 60], // Immatriculation
        ['x' => 135, 'y' => 65], // Kilométrage
        ['x' => 80, 'y' => 136.5], // Nom et téléphone du vendeur
        ['x' => 22, 'y' => 141.5], // Notes vendeur
        ['x' => 80, 'y' => 147.5], // Nom et téléphone de l'acheteur
        ['x' => 22, 'y' => 153], // Notes acheteur
        // ['x' => 52, 'y' => 147], // Carte grise titulaire
        // ['x' => 52, 'y' => 154], // Carte grise co-titulaire
        ['x' => 55, 'y' => 201.5], // Cashsentinel
        ['x' => 135, 'y' => 201.5], // Date de livraison possible
    ];

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(11);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    foreach ($crossToInsert as $cross) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($cross['x'], $cross['y']);
        $pdf->Write(0, 'X');
    }

    $folder = "../../storage/sell/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $cleanBrand = cleanValue($resVehicule['vehicules_marque']);
    $cleanModel = cleanValue($resVehicule['vehicules_model']);
    $cleanImmatriculation = cleanValue($resVehicule['vehicules_immatriculation']);
    $cleanNom = cleanValue($resClientVendeur['clients_nom']);
    $cleanPrenom = cleanValue($resClientVendeur['clients_prenom']);

    $cleanedValueName = $cleanNom . '-' . $cleanPrenom;
    $cleanedValueVehicule = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/DOCUMENTS_DE_VENTE/CLIENT_VENDEUR/';

    $pattern = $folder . "DOSSIER_DE_VENTE_" . $cleanedValueName . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "DOSSIER_DE_VENTE_" . $cleanedValueName . "_" . $fileCount . ".pdf";
    $destinationPath = $folder . $pdfNameFile;

    $getAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $getAgence->execute([intval($_SESSION['user']["agence_id"])]);
    $getAgence = $getAgence->fetch();
    $DBB->closeConnection();

    $pdf->Output('F', $destinationPath);
    // uploadPdfToNextcloud($getAgence['agence_path_client'], $cleanedValueName, $destinationPath);
    // uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $cleanedValueVehicule, $destinationPath);
    $pdf->Output('I', $pdfNameFile);
?>