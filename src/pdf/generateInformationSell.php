<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if (!isset($_SESSION['user']) || empty($_COOKIE['user_session']) || empty($_SESSION['user']['agence_id'])) {
        echo '
            <script>
                alert("Erreur 403 : Accès interdit. Veuillez vous connecter pour accéder à cette page.");
                window.location.href = "../../";
            </script>
        ';
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
        echo '
            <script>
                alert("Impossible de trouver le client.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }
    
    if(!empty($_POST['typeVehicle']) && !empty($_POST['numSerie'] && !empty($_POST['idVehicule']))) {
        $updateVehicules = $DB->prepare("UPDATE vehicules SET vehicules_type = ?, vehicules_numero_serie = ? WHERE vehicules_id = ?");
        $updateVehicules->execute([$_POST['typeVehicle'], $_POST['numSerie'], $_POST['idVehicule']]);
    }
    
    if(!$resClient || empty($_POST['immatCar'])) {
        echo '
            <script>
                alert("Impossible de trouver le client ou la plaque d\'immatriculation est invalide.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }
    
    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute([$_SESSION['user']['agence_id']]);
    $resAgence = $resAgence->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/information_sell.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importPDFData = [
        [ 'value' => $resClient['clients_nom'] . ' ' . $resClient['clients_prenom'], 'x' => 55, 'y' => 71.5 ],
        [ 'value' => $resClient['clients_rue'] . ' ' . $resClient['clients_ville'] . ' ' . $resClient['clients_cp'], 'x' => 55, 'y' => 76 ],
        [ 'value' => $resClient['clients_numero_cni'], 'x' => 63, 'y' => 81 ],
        [ 'value' => $resVehicule['vehicules_marque'], 'x' => 45, 'y' => 101 ],
        [ 'value' => $resVehicule['vehicules_model'], 'x' => 45, 'y' => 105.5 ],
        [ 'value' => $resVehicule['vehicules_type'], 'x' => 45, 'y' => 110 ],
        [ 'value' => $resVehicule['vehicules_immatriculation'], 'x' => 58, 'y' => 115 ],
        [ 'value' => $_POST['kilometrage'], 'x' => 51, 'y' => 120 ],
        [ 'value' => $resVehicule['vehicules_couleur'], 'x' => 135, 'y' => 100.5 ],
        [ 'value' => $_POST['puissanceFiscale'], 'x' => 150, 'y' => 105.5 ],
        [ 'value' => $resVehicule['vehicules_numero_serie'], 'x' => 140, 'y' => 110 ],
        [ 'value' => date('d/m/Y', strtotime($resVehicule['vehicules_date_mise_en_circu'])), 'x' => 140, 'y' => 115 ],
        [ 'value' => date('d/m/Y', strtotime($_POST['dernierControleTechnique'])), 'x' => 125, 'y' => 159.5 ],
        [ 'value' => $_POST['pvNum'], 'x' => 155, 'y' => 159.5 ], 
        [ 'value' => $resAgence['agence_nom'], 'x' => 52, 'y' => 219.5 ],
        [ 'value' => date('d/m/Y'), 'x' => 88, 'y' => 219.5 ]
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