<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(empty($_SESSION['user']) || empty($_COOKIE['user_session'])) {
        header('Location: ../../login.php');
        exit();
    } else if (empty($_POST['customerMail']) || empty($_POST['immatricuCar'])) {
        header('Location: ../../index.php');
        exit();
    }

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../functions/createFolderNextCloud.php';
    require_once '../../database.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatricuCar']]);
    $resVehicule = $resVehicule->fetch();

    $resClient = $DB->prepare('SELECT * FROM clients INNER JOIN agence ON agence.agence_id = clients.clients_agence_id WHERE clients.clients_email = ?');
    $resClient->execute([$_POST['customerMail']]);
    $resClient = $resClient->fetch();

    $resUser = $DB->prepare('SELECT * FROM utilisateurs WHERE utilisateurs_id = ?');
    $resUser->execute([$_SESSION['user']['id']]);
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

    //Valeur dans la BDD
    $importVarPDF = [
        $formattedId,
        strtoupper($resUser['utilisateurs_nom']) . " " . $resUser['utilisateurs_prenom'],
        strtoupper($resClient['clients_nom']) . " " . $resClient['clients_prenom'],
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
        date('d/m/Y', strtotime($resVehicule['vehicules_date_entretien'])),
        $_POST['jourVisite'],
        $_POST['prixVente'],
        $_POST['raisonVente'],
        $_POST['delayVenteText'] . " " . $_POST['delayVenteType'],
        $_POST['prixVenteSouhaite'],
        ucfirst($resClient['agence_nom']),
        date('d/m/Y'),
        date('d/m/Y', strtotime($resVehicule['vehicules_date_mise_en_circu']))
    ];

    $importCoordinates = [
        ['x' => 33, 'y' => 55], //ID
        ['x' => 132, 'y' => 55], //Collaborateur
        ['x' => 56, 'y' => 63], //Nom prénom
        ['x' => 65, 'y' => 79], //numCNI
        ['x' => 116, 'y' => 87], //tel
        ['x' => 70, 'y' => 97], //immat
        ['x' => 43, 'y' => 106], //model
        ['x' => 43, 'y' => 115], //type boite
        ['x' => 63, 'y' => 124], //finition
        ['x' => 60, 'y' => 134], //nbr Mains
        ['x' => 63, 'y' => 143], //orginCar
        ['x' => 55, 'y' => 152], //frais recent
        ['x' => 55, 'y' => 160], //frais prevoir
        ['x' => 116, 'y' => 79], //email
        ['x' => 127, 'y' => 97], //marque
        ['x' => 120, 'y' => 106], //puissance
        ['x' => 125, 'y' => 115], //couleur
        ['x' => 122, 'y' => 124], //kilometrage
        ['x' => 146, 'y' => 134], //date entretien
        ['x' => 55, 'y' => 87], //jourvisite
        ['x' => 107, 'y' => 172], //prix vente
        ['x' => 60, 'y' => 180], //raison vente
        ['x' => 150, 'y' => 180], //delay vente
        ['x' => 80, 'y' => 193], //prix Vente Souhaite
        ['x' => 32, 'y' => 255], //Agence nom
        ['x' => 75, 'y' => 255], //Date
        ['x' => 135, 'y' => 143] //Mise en circu
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

    $toCleanVehicule = $resVehicule['vehicules_marque'] . '/'. $resVehicule['vehicules_model'] . '-' . strtoupper($resVehicule['vehicules_immatriculation']) . '/';

    $cleanedValueNameFolder = preg_replace('/[^A-Za-z0-9]+/', '-', trim($resClient['clients_nom'] . " " . $resClient['clients_prenom']));
    $cleanedValueName = preg_replace('/[^A-Za-z0-9]+/', '_', trim($resClient['clients_nom'] . " " . $resClient['clients_prenom']));
    $cleanedValueNameVehicule = $toCleanVehicule . "DOCUMENTS_DE_VENTE/CLIENT_VENDEUR";

    $pattern = $folder . "MANDAT_DE_VENTE_" . strtoupper($cleanedValueName) . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "MANDAT_DE_VENTE_" . strtoupper($cleanedValueName) . "_" . $fileCount . ".pdf";
    $destinationPath = $folder . $pdfNameFile;

    $getAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $getAgence->execute([intval($_SESSION['user']["agence_id"])]);
    $getAgence = $getAgence->fetch();
    $DBB->closeConnection();

    $pdf->Output('F', $destinationPath);
    uploadPdfToNextcloud($getAgence['agence_path_client'], strtoupper($cleanedValueNameFolder), $destinationPath);
    uploadPdfToNextcloud($getAgence['agence_path_vehicules'], strtoupper($cleanedValueNameVehicule), $destinationPath);
    $pdf->Output('I', $pdfNameFile);
?>