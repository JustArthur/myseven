<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    } else if (empty($_POST['customerMail']) || empty($_POST['immatricuCar'])) {
        header('Location: ../../index.php');
        exit();
    }

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../database.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatricuCar']]);
    $resVehicule = $resVehicule->fetch();

    $resClient = $DB->prepare('SELECT * FROM clients INNER JOIN agence ON agence.agence_id = clients.clients_agence_id WHERE clients.clients_email = ?');
    $resClient->execute([$_POST['customerMail']]);
    $resClient = $resClient->fetch();

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

    //Valeur dans la BDD
    $importVarPDF = [
        $formattedId,
        $resClient['clients_nom'] . " " . $resClient['clients_prenom'],
        $resClient['clients_numero_cni'],
        $resClient['clients_telephone'],
        $resVehicule['vehicules_immatriculation'],
        $resVehicule['vehicules_model'],
        $resVehicule['vehicules_type_boite'],
        $resVehicule['vehicules_finition'],
        $_POST['nbrMains'],
        $_POST['originCar'],
        $resVehicule['vehicules_frais_recent'],
        $resVehicule['vehicules_frais_prevoir'],
        $resClient['clients_email'],
        $resVehicule['vehicules_marque'],
        $resVehicule['vehicules_puissance'],
        $resVehicule['vehicules_couleur'],
        $resVehicule['vehicules_kilometrage'],
        $resVehicule['vehicules_date_entretien'],
        $_POST['jourVisite'],
        $_POST['prixVente'],
        $_POST['raisonVente'],
        $_POST['delayVenteText'] . " " . $_POST['delayVenteType'],
        $_POST['prixVenteSouhaite'],
        ucfirst($resClient['agence_nom']),
        date('d/m/Y'),
    ];

    $importCoordinates = [
        ['x' => 33, 'y' => 55],
        ['x' => 56, 'y' => 63],
        ['x' => 65, 'y' => 79],
        ['x' => 40, 'y' => 87],
        ['x' => 70, 'y' => 97],
        ['x' => 43, 'y' => 106],
        ['x' => 43, 'y' => 115],
        ['x' => 63, 'y' => 124],
        ['x' => 60, 'y' => 134],
        ['x' => 63, 'y' => 143],
        ['x' => 55, 'y' => 152],
        ['x' => 55, 'y' => 160],
        ['x' => 122, 'y' => 87],
        ['x' => 127, 'y' => 97],
        ['x' => 120, 'y' => 105],
        ['x' => 125, 'y' => 115],
        ['x' => 122, 'y' => 124],
        ['x' => 152, 'y' => 134],
        ['x' => 134, 'y' => 143],
        ['x' => 107, 'y' => 173],
        ['x' => 60, 'y' => 181],
        ['x' => 150, 'y' => 182],
        ['x' => 80, 'y' => 193],
        ['x' => 32, 'y' => 255],
        ['x' => 75, 'y' => 255]
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/new_sale_mandate.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../storage/sale_mandates/";

    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $pattern = $folder . "MANDAT_DE_VENTE_" . $importVarPDF[1] . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "MANDAT_DE_VENTE_" . $importVarPDF[1] . "_" . $fileCount . ".pdf";

    $DBB->closeConnection();
    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
?>