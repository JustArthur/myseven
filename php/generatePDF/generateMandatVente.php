<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    if (!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../php/login.php');
        exit();
    }

    require_once('../../vendor/setasign/fpdf/fpdf.php');
    require_once('../../vendor/setasign/fpdi/src/autoload.php');

    require_once("../../connexionDB.php");

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE immatriculation = ?');
    $resVehicule->execute([$_POST['immatricuCar']]);
    $resVehicule = $resVehicule->fetch();

    $resClient = $DB->prepare('SELECT * FROM clients WHERE email = ?');
    $resClient->execute([$_POST['customerMail']]);
    $resClient = $resClient->fetch();

    $filePath = 'data.json';
    if (!file_exists($filePath)) {
        file_put_contents($filePath, json_encode(['count' => 0]));
    }

    $data = json_decode(file_get_contents($filePath), true);

    $data['count']++;

    $currentYear = date('y');
    $currentMonth = date('m');

    $formattedId = sprintf('%s%s-%03d', $currentYear, $currentMonth, $data['count']);

    file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

    //Valeur dans la BDD
    $importVarPDF = [
        $formattedId,
        $resClient['nom'] . " " . $resClient['prenom'],
        $resClient['numero_cni'],
        $resClient['telephone'],
        $resVehicule['immatriculation'],
        $resVehicule['model'],
        $resVehicule['type_boite'],
        $resVehicule['finition'],
        $_POST['nbrMains'],
        $_POST['originCar'],
        $resVehicule['frais_recent'],
        $resVehicule['frais_prevoir'],
        $resClient['email'],
        $resVehicule['marque'],
        $resVehicule['puissance'],
        $resVehicule['couleur'],
        $resVehicule['kilometrage'],
        $resVehicule['date_entretien'],
        $_POST['jourVisite'],
        $_POST['prixVente'],
        $_POST['raisonVente'],
        $_POST['delayVenteText'] . " " . $_POST['delayVenteType'],
        $_POST['prixVenteSouhaite'],
        ucfirst($resClient['agence']), //Lieu agence
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

    $pdfNameFile = "MANDAT DE VENTE " . $_POST['immatricuCar'] . ".pdf";

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../pdf/MANDAT DE VENTE NOUVEAU.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../PDF_saved/MandatVente/";

    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
    $DBB->closeConnection();
?>