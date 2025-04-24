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

    if (!isset($_SESSION['user']) || empty($_COOKIE['user_session']) || empty($_SESSION['user']['agence_id'])) {
        echo '
            <script>
                alert("Erreur 403 : Accès interdit. Veuillez vous connecter pour accéder à cette page.");
                window.location.href = "../../login.php";
            </script>
        ';
        exit();
    }

    if (empty($_POST['idClientVendeur']) || empty($_POST['idClientAcheteur']) || empty($_POST['immatCar'])) {
        echo '
            <script>
                alert("Impossible de trouver le client acheteur ou vendeur, ou la plaque d\'immatriculation.");
                window.close()
            </script>
        ';
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
        echo '
            <script>
                alert("Impossible de trouver le client acheteur ou vendeur, ou la plaque d\'immatriculation.");
                window.close()
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

    if (!empty($_POST['idCotitulaireVendeur'])) {
        $ids = $_POST['idCotitulaireVendeur'];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT cotitulaires_nom, cotitulaires_prenom FROM cotitulaires WHERE cotitulaires_id IN ($placeholders)";
        $stmt = $DB->prepare($sql);
        $stmt->execute($ids);
        $cotitulaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedNames = array_map(function($c) {
            return $c['cotitulaires_nom'] . ' ' . $c['cotitulaires_prenom'];
        }, $cotitulaires);
    
        $cotitulairesString = implode(' / ', $formattedNames);
    } else {
        $cotitulairesString = "";
    }

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/sell.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $crossToInsert = [];
    
    switch ($_POST['garantie']) {
        case 'allRisk': $crossToInsert[] = ['x' => 42, 'y' => 223.5]; break;
        case 'compelete': $crossToInsert[] = ['x' => 66.5, 'y' => 223.5]; break;
        case 'essentiel': $crossToInsert[] = ['x' => 87, 'y' => 223.5]; break;
        case 'mbp': $crossToInsert[] = ['x' => 108.5, 'y' => 223.5]; break;
    }

    if ($_POST['askGarantieConstructeur'] === 'yes') {
        $crossToInsert[] = ['x' => 42, 'y' => 228];
    } else {
        $_POST['dureeGarantieConstructeur'] = 'none';
    }

    switch ($_POST['dureeGarantieConstructeur']) {
        case '3mois': $crossToInsert[] = ['x' => 108.5, 'y' => 228]; break;
        case '6mois': $crossToInsert[] = ['x' => 124.5, 'y' => 228]; break;
        case '12mois': $crossToInsert[] = ['x' => 140, 'y' => 228]; break;
        case '24mois': $crossToInsert[] = ['x' => 157.5, 'y' => 228]; break;
    }

    switch ($_POST['typePaiement']) {
        case 'arrhes': $_POST['avanceInter'] = 'none'; break;
        case 'avanceInter': $_POST['arrhes'] = 'none'; break;
    }

    switch ($_POST['arrhes']) {
        case 'CB': $crossToInsert[] = ['x' => 149, 'y' => 244]; break;
        case 'cheque': $crossToInsert[] = ['x' => 169, 'y' => 244]; break;
    }

    switch ($_POST['avanceInter']) {
        case 'virement': $crossToInsert[] = ['x' => 141, 'y' => 253.5]; break;
        case 'cash': $crossToInsert[] = ['x' => 158.5, 'y' => 253.5]; break;
    }

    $importPDFData = [
        ['value' => $resVehicule['vehicules_marque'], 'x' => 45, 'y' => 55],
        ['value' => $resVehicule['vehicules_model'], 'x' => 45, 'y' => 60],
        ['value' => date('d/m/Y', strtotime($resVehicule['vehicules_date_mise_en_circu'])), 'x' => 45, 'y' => 65],
        ['value' => $resVehicule['vehicules_couleur'], 'x' => 135, 'y' => 55],
        ['value' => $resVehicule['vehicules_immatriculation'], 'x' => 135, 'y' => 60],
        ['value' => $resVehicule['vehicules_kilometrage'], 'x' => 135, 'y' => 65],
        ['value' => $resClientVendeur['clients_nom'] . ' ' . $resClientVendeur['clients_telephone'], 'x' => 80, 'y' => 136.5],
        ['value' => $_POST['notesClientAcheteur'], 'x' => 22, 'y' => 141.5],
        ['value' => $resClientAcheteur['clients_nom'] . ' ' . $resClientAcheteur['clients_telephone'], 'x' => 80, 'y' => 147.5],
        ['value' => $_POST['notesClientVendeur'], 'x' => 22, 'y' => 153],
        ['value' => ": " . $resClientVendeur['clients_nom'] . ' ' . $resClientVendeur['clients_prenom'], 'x' => 65, 'y' => 163],
        ['value' => ": " . $cotitulairesString, 'x' => 70, 'y' => 169],
        ['value' => date('d/m/Y', strtotime($_POST['dateCashSentinel'])), 'x' => 55, 'y' => 201.5],
        ['value' => date('d/m/Y', strtotime($_POST['dateLivraisonPossible'])), 'x' => 135, 'y' => 201.5],
        ['value' => $_POST['prixNETVendeur'] . '€', 'x' => 32, 'y' => 238.5],
        ['value' => $_POST['prixVenteGarantie'] . '€', 'x' => 82, 'y' => 238.5],
        ['value' => $_POST['prixVenteVoiture'] . '€', 'x' => 32, 'y' => 243.5],
        ['value' => $_POST['prixAgenceGarantie'] . '€', 'x' => 82, 'y' => 243.5],
        ['value' => $_POST['fraisMiseRoute'] . '€', 'x' => 32, 'y' => 248.5],
        ['value' => $_POST['prixVenteCarteGrise'] . '€', 'x' => 82, 'y' => 248.5],
        ['value' => $_POST['prixLivraison'] . '€', 'x' => 44, 'y' => 258],
        ['value' => $_POST['prixAchatLivraison'] . '€', 'x' => 96, 'y' => 258],
        ['value' => date('d/m/Y'), 'x' => 132, 'y' => 258]
    ];

    foreach ($importPDFData as $item) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(11);
        $pdf->SetXY($item['x'], $item['y']);
        $text = mb_convert_encoding($item['value'], 'windows-1252', 'UTF-8');
        $pdf->Write(0, $text);
    }

    foreach ($crossToInsert as $cross) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($cross['x'], $cross['y']);
        $pdf->Write(0, 'X');
    }

    $cleanBrand = cleanValue($resVehicule['vehicules_marque']);
    $cleanModel = cleanValue($resVehicule['vehicules_model']);
    $cleanImmatriculation = cleanValue($resVehicule['vehicules_immatriculation']);
    $cleanNom = cleanValue($resClientVendeur['clients_nom']);
    $cleanPrenom = cleanValue($resClientVendeur['clients_prenom']);

    $folder = "../../storage/" . $cleanNom . "-" . $cleanPrenom . "/dossier_de_vente/";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

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
    uploadPdfToNextcloud($getAgence['agence_path_vehicules'], $cleanedValueVehicule, $destinationPath);
    $pdf->Output('I', $pdfNameFile);
?>