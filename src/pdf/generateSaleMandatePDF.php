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
    
    if (empty($_POST['idClient']) || empty($_POST['immatCar'])) {
        echo '
            <script>
                alert("Impossible de trouver le client ou la plaque d\'immatriculation est invalide.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../functions/createFolderNextCloud.php';
    require_once '../../database.php';
    require_once '../functions/cleanValues.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_id = ?');
    $resClient->execute([$_POST['idClient']]);
    $resClient = $resClient->fetch();

    if(!$resClient || empty($_POST['immatCar'])) {
        echo '
            <script>
                alert("Impossible de trouver le client ou la plaque d\'immatriculation.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    } else if ($resClient['clients_type'] != 'Vendeur') {
        echo '
            <script>
                alert("Attention ce client n\'est pas un vendeur.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute([intval($_SESSION['user']["agence_id"])]);
    $resAgence = $resAgence->fetch();

    $resUser = $DB->prepare('SELECT * FROM utilisateurs WHERE utilisateurs_id = ?');
    $resUser->execute([intval($_SESSION['user']["id"])]);
    $resUser = $resUser->fetch();

    $filePath = '../../storage/json_data/sale_mandate_id.json';

    $directory = dirname($filePath);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    if (!file_exists($filePath)) {
        file_put_contents($filePath, json_encode(['count' => 0]));
    }

    $data = json_decode(file_get_contents($filePath), true);
    $data['count']++;
    $formattedId = sprintf('%s%s-%03d', date('y'), date('m'), $data['count']);
    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

    if (empty($resVehicule['vehicules_nombre_main'])) {
        $resVehicule['vehicules_nombre_main'] = $_POST['nbrMains'];
        $updateVehicule = $DB->prepare('UPDATE vehicules SET vehicules_nombre_main = ? WHERE vehicules_id = ?');
        $updateVehicule->execute([$resVehicule['vehicules_nombre_main'], $resVehicule['vehicules_id']]);
    }

    if(empty($resVehicule['vehicules_origine'])) {
        $resVehicule['vehicules_origine'] = $_POST['originCar'];
        $updateVehicule = $DB->prepare('UPDATE vehicules SET vehicules_origine = ? WHERE vehicules_id = ?');
        $updateVehicule->execute([$_POST['originCar'], $resVehicule['vehicules_id']]);
    }

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/new_sale_mandate.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importPDFData = [
        ['value' => $formattedId, 'x' => 33, 'y' => 54],
        ['value' => strtoupper($resUser['utilisateurs_nom']) . " " . $resUser['utilisateurs_prenom'], 'x' => 132, 'y' => 55],
        ['value' => strtoupper($resClient['clients_nom']) . " " . $resClient['clients_prenom'], 'x' => 56, 'y' => 63],
        ['value' => $resClient['clients_numero_cni'], 'x' => 65, 'y' => 79],
        ['value' => $resClient['clients_telephone'], 'x' => 116, 'y' => 87],
        ['value' => $resVehicule['vehicules_immatriculation'], 'x' => 70, 'y' => 97],
        ['value' => $resVehicule['vehicules_model'], 'x' => 43, 'y' => 106],
        ['value' => $resVehicule['vehicules_type_boite'], 'x' => 43, 'y' => 115],
        ['value' => $resVehicule['vehicules_finition'], 'x' => 63, 'y' => 124],
        ['value' => $resVehicule['vehicules_nombre_main'], 'x' => 60, 'y' => 134],
        ['value' => $resVehicule['vehicules_origine'], 'x' => 63, 'y' => 143],
        ['value' => $resVehicule['vehicules_frais_recent'], 'x' => 50, 'y' => 152],
        ['value' => $resVehicule['vehicules_frais_prevoir'], 'x' => 50, 'y' => 160],
        ['value' => $resClient['clients_email'], 'x' => 116, 'y' => 79],
        ['value' => $resVehicule['vehicules_marque'], 'x' => 122, 'y' => 97],
        ['value' => $resVehicule['vehicules_puissance'], 'x' => 113, 'y' => 106],
        ['value' => $resVehicule['vehicules_couleur'], 'x' => 118, 'y' => 115],
        ['value' => $resVehicule['vehicules_kilometrage'], 'x' => 115, 'y' => 124],
        ['value' => date('d/m/Y', strtotime($resVehicule['vehicules_date_entretien'])), 'x' => 146, 'y' => 134],
        ['value' => $_POST['jourVisite'], 'x' => 50, 'y' => 87],
        ['value' => $_POST['prixVente'], 'x' => 107, 'y' => 172],
        ['value' => $_POST['raisonVente'], 'x' => 60, 'y' => 180],
        ['value' => $_POST['delayVenteText'] . " " . $_POST['delayVenteType'], 'x' => 150, 'y' => 180],
        ['value' => $_POST['prixVenteSouhaite'], 'x' => 80, 'y' => 193],
        ['value' => ucfirst($resAgence['agence_nom']), 'x' => 32, 'y' => 254],
        ['value' => date('d/m/Y'), 'x' => 75, 'y' => 254],
        ['value' => date('d/m/Y', strtotime($resVehicule['vehicules_date_mise_en_circu'])), 'x' => 135, 'y' => 143],
    ];

    foreach ($importPDFData as $item) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(10);
        $pdf->SetXY($item['x'], $item['y']);
        $text = mb_convert_encoding($item['value'], 'windows-1252', 'UTF-8');
        $pdf->Write(0, $text);
    }

    $cleanBrand = cleanValue($resVehicule['vehicules_marque']);
    $cleanModel = cleanValue($resVehicule['vehicules_model']);
    $cleanImmatriculation = cleanValue($resVehicule['vehicules_immatriculation']);
    $cleanNom = cleanValue($resClient['clients_nom']);
    $cleanPrenom = cleanValue($resClient['clients_prenom']);

    $folder = "../../storage/" . $cleanNom . "-" . $cleanPrenom . "/mandat_de_vente/";

    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $cleanedValueName = $cleanNom . '-' . $cleanPrenom;
    $cleanedValueVehicule = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/DOCUMENTS_DE_VENTE/CLIENT_VENDEUR/';

    $pattern = $folder . "MANDAT_DE_VENTE_" . strtoupper($cleanedValueName) . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "MANDAT_DE_VENTE_" . strtoupper($cleanedValueName) . "_" . $fileCount . ".pdf";
    $destinationPath = $folder . $pdfNameFile;

    $DBB->closeConnection();

    $pdf->Output('F', $destinationPath);
    // uploadPdfToNextcloud($resAgence['agence_path_client'], strtoupper($cleanedValueName), $destinationPath);
    uploadPdfToNextcloud($resAgence['agence_path_vehicules'], strtoupper($cleanedValueVehicule), $destinationPath);
    $pdf->Output('I', $pdfNameFile);
?>