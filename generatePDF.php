<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require_once('../vendor/setasign/fpdf/fpdf.php');
    require_once('../vendor/setasign/fpdi/src/autoload.php');

    require_once( "../connexionDB.php");

    date_default_timezone_set('Europe/Paris');
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);

    $fields = ['nbrMains', 'originCar', 'jourVisite', 'prixVente', 'raisonVente', 'delayVenteText', 'prixVenteSouhaite', 'immatCar', 'customerMail'];

    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            header('Location: error.php?error=empty');
            exit();
        }
    }

    if($_POST['delayVenteText'] <= 0) {
        header('Location: error.php?error=delay');
        exit();
    }

    if($_POST['jourVisite'] > date('Y-m-d')) {
        header('Location: error.php?error=date');
        exit();
    }
    

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();
    
    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    $resClient = $DB->prepare('SELECT * FROM Clients WHERE email = ?');
    $resClient->execute([$_POST['customerMail']]);
    $resClient = $resClient->fetch();


    //Verif de si le client existe dans la BDD
    if(!isset($resClient['email']) && empty($resClient['email'])) {
        header('Location: error.php?error=client');
        $DBB->closeConnection();
        exit();
    }



    $filePath = 'data.json';
    if (!file_exists($filePath)) { file_put_contents($filePath, json_encode(['count' => 0]));}

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
        $formatter->format(time()),
    ];

    //Valeur Y
    $importY = [
        55, //Num PDF
        63, // Nom Prénom
        79, //Numero CNI
        87, //tel
        97, //immat
        106, //model
        115, //type boite
        124, //finition
        134, //nbrMains
        143, //origine véhicule
        152, //Frais recent
        160, //frais prévoir
        87, //email
        97, //marque
        105, //puissance
        115, //couleur
        124, //kilome
        134, //date entre
        143, //jour visite
        173, //prix vente
        181, //raison vente
        182,// delay vente
        193, //prix de vente souhaité
        255, // Lieu
        255 //Date
    ];

    //Valeur X
    $importX = [
        33,//Num PDF
        56,// Nom Prénom
        65,//Numero CNI
        40,//tel
        70,//immat
        43,//model
        43,//type boite
        63,//finition
        60,//nbrMains
        63,//origine véhicule
        55,//Frais recent
        55,//frais prévoir
        122,//email
        127,//marque
        120,//puissance
        125,//couleur
        122,//kilome
        152,//date entre
        134,//jour visite
        107,//prix vente
        60,//raison vente
        150,// delay vente
        80, //prix de vente souhaité
        32,// Lieu
        75 //Date
    ];

    $pdfNameFile = "MANDAT DE VENTE " . $_POST['immatCar'];

    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../pdf/MANDAT DE VENTE NOUVEAU.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    $i = 0;
    foreach($importVarPDF as $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($importX[$i], $importY[$i]);
        $pdf->Write(0, $importVarPDF[$i]);
        $i++;
    }

    $pdf->Output('I', $pdfNameFile);
    $DBB->closeConnection();
?>