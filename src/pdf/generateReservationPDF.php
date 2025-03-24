<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    } else if (empty($_POST['customerMail']) || empty($_POST['immatCar'])) {
        header('Location: ../../index.php');
        exit();
    }

    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';
    require_once '../../database.php';


    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();
  
    $resClient = $DB->prepare('SELECT * FROM clients INNER JOIN agence ON clients.clients_agence_id = agence.agence_id WHERE clients.clients_email = ?');
    $resClient->execute(params: [$_POST['customerMail']]);
    $resClient = $DB->prepare('SELECT * FROM clients INNER JOIN agence ON clients.clients_agence_id = agence.agence_id WHERE clients.clients_email = ?');
    $resClient->execute([$_POST['customerMail']]);
    $resClient = $resClient->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute(params: [$_POST['immatCar']]);
    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute(params: [$resClient['clients_agence_id']]);
    $resAgence = $resAgence->fetch();

    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute([$resClient['clients_agence_id']]);
    $resAgence = $resAgence->fetch();

    $crossToCreate = [];

    switch($_POST['garantieMecaniqueType']) {
        case '3Mois':
            array_push($crossToCreate, ['x' => 11.5, 'y' => 123.5]);
            break;

        case '12Mois':
            array_push($crossToCreate, ['x' => 11.5, 'y' => 106]);
            break;

        case '12MoisPrestige':
            array_push($crossToCreate, ['x' => 11.5, 'y' => 112]);
            break;

        case '24Mois':  
            array_push($crossToCreate, ['x' => 11.5, 'y' => 118]);
            break;

        case 'refuse':
            array_push($crossToCreate, ['x' => 98, 'y' => 106]);
            $_POST['garantieMecaniqueText'] = 0;
            array_push($crossToCreate, ['x' => 98, 'y' => 106]);
            break;

        default:
            array_push($crossToCreate, ['x' => 98, 'y' => 106]);
            $_POST['garantieMecaniqueText'] = 0;
            array_push($crossToCreate, ['x' => 98, 'y' => 106]);
            break;
    }

    switch($_POST['expertiseSouhaitee']) {
        case 'Oui':
            array_push($crossToCreate, ['x' => 50, 'y' => 173.5]);
            array_push($crossToCreate, ['x' => 50, 'y' => 173.5]);
            break;

        default:
            array_push($crossToCreate, ['x' => 60, 'y' => 173.5]);
            break;
    }

    switch($_POST['depot_arrhes_select']) {
        case 'empBank':
            array_push($crossToCreate, ['x' => 103, 'y' => 189]);
            break;

        case 'virBank':
            array_push($crossToCreate, ['x' => 103, 'y' => 196]);
            break;

        case 'cheqEsp':
            array_push($crossToCreate, ['x' => 103, 'y' => 202.5]);
            array_push($crossToCreate, ['x' => 60, 'y' => 173.5]);
            break;
    }

    switch($_POST['depot_arrhes_select']) {
        case 'empBank':
            array_push($crossToCreate, ['x' => 103, 'y' => 189]);
            break;

        case 'virBank':
            array_push($crossToCreate, ['x' => 103, 'y' => 196]);
            break;

        case 'cheqEsp':
            array_push($crossToCreate, ['x' => 103, 'y' => 202.5]);
            break;
    }

    $fraisMiseEnRoute = isset($_POST['fraisMiseEnRoute']) && !empty($_POST['fraisMiseEnRoute']) ? $_POST['fraisMiseEnRoute'] : 0;
    array_push($crossToCreate, ['x' => 18.5, 'y' => 148]);
    array_push($crossToCreate, ['x' => 18.5, 'y' => 148]);

    $prixTotalHCG = (int)$_POST['garantieMecaniqueText'] + (int)$fraisMiseEnRoute + (int)$_POST['PrixVehicule'] + (int)$_POST['livraison'];
    // $fraisCG = (int)$_POST['garantieMecaniqueText'] + $fraisMiseEnRoute + (int)$_POST['livraison'];
    $_POST['miseCircu'] = date('d/m/Y', strtotime($_POST['miseCircu']));

    $importVarPDF = [
        $resClient['clients_nom'] . ' ' . $resClient['clients_prenom'],
        $resClient['clients_rue'],
        $resClient['clients_cp'],
        $resClient['clients_ville'],
        $resClient['clients_telephone'],
        $resClient['clients_email'],
        $resVehicule['vehicules_marque'] . ' ' . $resVehicule['vehicules_model'],
        $resVehicule['vehicules_immatriculation'],
        $_POST['PrixVehicule'],
        $fraisMiseEnRoute,
        $_POST['garantieMecaniqueText'],
        $_POST['livraison'],
        $prixTotalHCG,
        $_POST['fraisGC'],
        $resClient['agence_nom'],
        date('d/m/Y'),
        $_POST['depot_arrhes_input'],
        $resAgence['agence_iban'],
        $resAgence['agence_bic'],
        $_POST['miseCircu']
    ];


    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/reservation_contract.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importCoordinates = [
        ['x' => 103, 'y' => 51], //nom prénom
        ['x' => 93, 'y' => 58], //adresse
        ['x' => 89, 'y' => 64], //cp
        ['x' => 115, 'y' => 64], //ville
        ['x' => 85, 'y' => 71], //telephone
        ['x' => 130, 'y' => 71], //email
        ['x' => 48, 'y' => 88], //marque model
        ['x' => 42, 'y' => 93.5], //immat
        ['x' => 41, 'y' => 183], //prix véhicule
        ['x' => 38, 'y' => 190], //frais mise à la route
        ['x' => 36, 'y' => 196], //graentie méca
        ['x' => 29, 'y' => 202], //Livraison
        ['x' => 65, 'y' => 217], //Prix total HCG
        ['x' => 65, 'y' => 223], //Frais CG
        ['x' => 20, 'y' => 263.5], //Agence
        ['x' => 70, 'y' => 263.5], // Date
        ['x' => 167, 'y' => 183], // Montant arrhes
        ['x' => 128, 'y' => 149], // IBAN
        ['x' => 128, 'y' => 153], // BIC
        ['x' => 130, 'y' => 93.5], // Mise en circulation
    ];

    foreach ($crossToCreate as $index) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($index['x'], $index['y']);
        $pdf->Write(0, 'X');
    }

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(r: 0, g: 0, b: 0);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../storage/reservations/";

    $pattern = $folder . "BON_RESERVATION_" . $importVarPDF[0] . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "BON_RESERVATION_" . $importVarPDF[0] . "_" . $fileCount . ".pdf";

    if(!file_exists(filename: $folder)) {
        mkdir($folder, 0777, true);
    }

    $DBB->closeConnection();
    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
?>