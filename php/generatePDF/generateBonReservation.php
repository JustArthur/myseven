<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    if(!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../php/login.php');
        exit();
    }

    require_once('../../vendor/setasign/fpdf/fpdf.php');
    require_once('../../vendor/setasign/fpdi/src/autoload.php');

    require_once("../../connexionDB.php");

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();
  
    $resClient = $DB->prepare('SELECT * FROM Clients WHERE email = ?');
    $resClient->execute([$_POST['customerMail']]);
    $resClient = $resClient->fetch();

    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $day = date("d");
    $month = date("m");
    $year = date("Y");

    $crossToCreate = [];

    switch($_POST['garantieMecanique']) {
        case '3Mois':
            array_push($crossToCreate, ['x' => 116, 'y' => 125]);
            $prixGarantie = 0;
            break;

        case '12Mois':
            array_push($crossToCreate, ['x' => 23, 'y' => 119]);
            $prixGarantie = 890;
            break;

        case '12MoisPrestige':
            array_push($crossToCreate, ['x' => 23, 'y' => 125]);
            $prixGarantie = 1490;
            break;

        case '24Mois':  
            array_push($crossToCreate, ['x' => 104, 'y' => 119]);
            $prixGarantie = 1490;
            break;

        case 'refuse':
            array_push($crossToCreate, ['x' => 23, 'y' => 131]);
            $prixGarantie = 0;
            break;

        default:
            array_push($crossToCreate, ['x' => 23, 'y' => 131]);
            $prixGarantie = 0;
            break;
    }

    switch($_POST['fraisMiseEnRoute']) {
        case 'Oui':
            array_push($crossToCreate, ['x' => 24, 'y' => 158.5]);
            $fraisMiseEnRoute = 690;
            break;

        default:
            $fraisMiseEnRoute = 0;
            break;
    }

    switch($_POST['expertiseSouhaitee']) {
        case 'Oui':
            array_push($crossToCreate, ['x' => 56, 'y' => 184]);
            break;

        default:
            array_push($crossToCreate, ['x' => 66.5, 'y' => 184]);
            break;
    }

    $prixTotalHCG = $prixGarantie + $fraisMiseEnRoute + (int)$_POST['PrixVehicule'] + (int)$_POST['livraison'];
    $fraisCG = $prixGarantie + $fraisMiseEnRoute + (int)$_POST['livraison'];

    $importVarPDF = [
        $resClient['nom'] . ' ' . $resClient['prenom'],
        $resClient['adresse'],
        $resClient['cp'],
        $resClient['ville'],
        $resClient['telephone'],
        $resClient['email'],
        $resVehicule['marque'] . ' ' . $resVehicule['model'],
        $resVehicule['immatriculation'],
        $_POST['PrixVehicule'],
        $fraisMiseEnRoute,
        $prixGarantie,
        $_POST['livraison'],
        $resVehicule['marque'] . ' ' . $resVehicule['model'],
        $resVehicule['immatriculation'],
        $resVehicule['kilometrage'],
        //MEC
        //Prix de reprise
        $prixTotalHCG,
        $fraisCG,
        $resClient['agence'],
        date('d/m/Y')
    ];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../pdf/BON_DE_RESERVATION_2023_CAMBRAI.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $importCoordinates = [
        ['x' => 103, 'y' => 51], //nom prénom
        ['x' => 93, 'y' => 58], //adresse
        ['x' => 98, 'y' => 65], //cp
        ['x' => 130, 'y' => 65], //ville
        ['x' => 85, 'y' => 72], //telephone
        ['x' => 130, 'y' => 72], //email
        ['x' => 42, 'y' => 97], //marque model
        ['x' => 42, 'y' => 103], //immat
        ['x' => 41, 'y' => 189.5], //prix véhicule
        ['x' => 47, 'y' => 196.5], //frais mise à la route
        ['x' => 47, 'y' => 203.5], //graentie méca
        ['x' => 29, 'y' => 210], //Livraison
        ['x' => 143, 'y' => 191], //marque model
        ['x' => 140, 'y' => 196], //immat
        ['x' => 136, 'y' => 201], //kilometrage
        //MEC
        //Prix de reprise
        ['x' => 65, 'y' => 217], //Prix total HCG
        ['x' => 65, 'y' => 223], //Frais CG
        ['x' => 20, 'y' => 264], //Agence
        ['x' => 70, 'y' => 264], // Date
    ];

    foreach ($crossToCreate as $index) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($index['x'], $index['y']);
        $pdf->Write(0, 'X');
    }

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../PDF_saved/BonReservation/";

    $fileCount = count(glob($folder . "*.pdf")) + 1;
    $pdfNameFile = "BON_RESERVATION_" . $importVarPDF[1] . "_" . $fileCount . ".pdf";

    if(!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $pdf->Output('I', $pdfNameFile);
    $pdf->Output('F', $folder . $pdfNameFile);
    $DBB->closeConnection();
?>