<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(empty($_SESSION['user']) || empty($_COOKIE['user_session'])) {
        header('Location: ../../login.php');
        exit();
    } 
    
    if (empty($_POST['idClient']) || empty($_POST['immatCar'])) {
        header('Location: ../../index.php');
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
        header('Location: ../../index.php');
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
        $resVehicule['vehicules_nombre_main'],
        $resVehicule['vehicules_origine'],
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
        ucfirst($resAgence['agence_nom']),
        date('d/m/Y'),
        date('d/m/Y', strtotime($resVehicule['vehicules_date_mise_en_circu']))
    ];

    $importCoordinates = [
        ['x' => 33, 'y' => 54], //ID
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
        ['x' => 50, 'y' => 152], //frais recent
        ['x' => 50, 'y' => 160], //frais prevoir
        ['x' => 116, 'y' => 79], //email
        ['x' => 122, 'y' => 97], //marque
        ['x' => 113, 'y' => 106], //puissance
        ['x' => 118, 'y' => 115], //couleur
        ['x' => 115, 'y' => 124], //kilometrage
        ['x' => 146, 'y' => 134], //date entretien
        ['x' => 50, 'y' => 87], //jour visite
        ['x' => 107, 'y' => 172], //prix vente
        ['x' => 60, 'y' => 180], //raison vente
        ['x' => 150, 'y' => 180], //delay vente
        ['x' => 80, 'y' => 193], //prix Vente Souhaite
        ['x' => 32, 'y' => 254], //Agence nom
        ['x' => 75, 'y' => 254], //Date du jour
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
        $pdf->SetFontSize(10);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../storage/sale_mandates/";

    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $cleanBrand = cleanValue($resVehicule['vehicules_marque']);
    $cleanModel = cleanValue($resVehicule['vehicules_model']);
    $cleanImmatriculation = cleanValue($resVehicule['vehicules_immatriculation']);
    $cleanNom = cleanValue($resClient['clients_nom']);
    $cleanPrenom = cleanValue($resClient['clients_prenom']);

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