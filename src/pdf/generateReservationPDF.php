<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    // ====== Vérification de la session ====== //
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

    
    // ====== Inclusion des fichiers nécessaires ====== //
    require_once '../../vendor/setasign/fpdf/fpdf.php';
    require_once '../../vendor/setasign/fpdi/src/autoload.php';

    require_once '../../database.php';
    require_once '../functions/createFolderNextCloud.php';
    require_once '../functions/cleanValues.php';


    // ====== Connexion à la base de données ====== //
    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    // ====== Récupération des informations du client ====== //
    $resClient = $DB->prepare('SELECT * FROM clients WHERE clients_id = ?');
    $resClient->execute([$_POST['idClient']]);
    $resClient = $resClient->fetch();

    // ===== Vérification de l'existence du client ====== //
    if(!$resClient || empty($_POST['immatCar'])) {
        echo '
            <script>
                alert("Impossible de trouver le client ou la plaque d\'immatriculation est invalide.");
                window.location.href = "../../";
            </script>
        ';
        exit();
    }

    // ====== Récupération des informations du véhicule ====== //
    $resVehicule = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_immatriculation = ?');
    $resVehicule->execute([$_POST['immatCar']]);
    $resVehicule = $resVehicule->fetch();

    // ====== Récupération des informations de l'agence ====== //
    $resAgence = $DB->prepare('SELECT * FROM agence WHERE agence_id = ?');
    $resAgence->execute([intval($_SESSION['user']["agence_id"])]);
    $resAgence = $resAgence->fetch();

    // ====== Récupération des informations si un dossier de vente à déjà été fait ou non ====== //
    $resInfo = $DB->prepare('SELECT informations_id FROM informations WHERE informations_clients_id = ? AND informations_vehicules_id = ?');
    $resInfo->execute([$resClient['clients_id'], $resVehicule['vehicules_id']]);
    $resInfo = $resInfo->fetch();

    // ====== Valeur par défaut ====== //
    $crossToCreate = [];
    $cashSentinel = "";

    // ====== Croix pour la garantie mécanique dans le PDF ====== //
    switch($_POST['garantieMecaniqueType']) {
        case '3Mois':
            array_push($crossToCreate, ['x' => 11.5, 'y' => 123.5]);
            break;

        case '12Mois':
            array_push($crossToCreate, ['x' => 11.5, 'y' => 106]);
            break;

        case '24Mois':
            array_push($crossToCreate, ['x' => 11.5, 'y' => 112]);
            break;
            
        case '12MoisPrestige':  
            array_push($crossToCreate, ['x' => 11.5, 'y' => 118]);
            break;

        case 'refuse':
            $_POST['garantieMecaniqueText'] = 0;
            array_push($crossToCreate, ['x' => 98, 'y' => 106]);
            break;

        default:
        $_POST['garantieMecaniqueText'] = 0;
            array_push($crossToCreate, ['x' => 98, 'y' => 106]);
            break;
    }

    // ====== Croix pour l'expertise souhaitee dans le PDF ====== //
    switch($_POST['expertiseSouhaitee']) {
        case 'Oui':
            array_push($crossToCreate, ['x' => 50, 'y' => 173.5]);
            break;

        default:
            array_push($crossToCreate, ['x' => 60, 'y' => 173.5]);
            break;
    }

    // ====== Croix pour le dépot arrhes dans le PDF ====== //
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

    // ====== Frais de la mise à la route ====== //
    $fraisMiseEnRoute = isset($_POST['fraisMiseEnRoute']) && !empty($_POST['fraisMiseEnRoute']) ? $_POST['fraisMiseEnRoute'] : 0;
    array_push($crossToCreate, ['x' => 18.5, 'y' => 148]);


    // ====== Prix Total HCG et CashSentinel ====== //
    if($_POST['depot_arrhes_select'] != 'empBank') {
        $prixTotalHCG = (int)$_POST['garantieMecaniqueText'] + (int)$fraisMiseEnRoute + (int)$_POST['PrixVehicule'] + (int)$_POST['livraison'];
        $cashSentinel = (int)$prixTotalHCG - (int)$_POST['depot_arrhes_input'];
    } else {
        $prixTotalHCG = (int)$_POST['garantieMecaniqueText'] + (int)$fraisMiseEnRoute + (int)$_POST['PrixVehicule'] + (int)$_POST['livraison'];
        $cashSentinel = $prixTotalHCG;
    }


    // ====== Valeur qui seront insert dans le PDF ====== //
    $importVarPDF = [
        strtoupper($resClient['clients_nom']) . ' ' . $resClient['clients_prenom'],
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
        $resAgence['agence_nom'],
        date('d/m/Y'),
        $_POST['depot_arrhes_input'] . " €",
        $resAgence['agence_iban'],
        $resAgence['agence_bic'],
        date('d/m/Y', strtotime($resVehicule['vehicules_date_mise_en_circu'])),
        $resAgence['agence_nom'],
        $resAgence['agence_nom'],
        $resAgence['agence_rue'],
        $resAgence['agence_cp'] . " " . $resAgence['agence_ville'],
        $resAgence['agence_telephone'],
        $resAgence['agence_mail'],
        "ARR " . $resVehicule['vehicules_immatriculation'],
        ": " . $cashSentinel . " € TTC",
    ];
    

    // ====== Coordonnées pour le PDF ====== //
    $importCoordinates = [
        ['x' => 103, 'y' => 52], //nom prénom
        ['x' => 93, 'y' => 58], //adresse
        ['x' => 94, 'y' => 64], //cp
        ['x' => 122, 'y' => 64], //ville
        ['x' => 85, 'y' => 71], //telephone
        ['x' => 130, 'y' => 71], //email
        ['x' => 48, 'y' => 88], //marque model
        ['x' => 42, 'y' => 93.5], //immat
        ['x' => 41, 'y' => 183], //prix véhicule
        ['x' => 45, 'y' => 190], //frais mise à la route
        ['x' => 45, 'y' => 196], //graentie méca
        ['x' => 29, 'y' => 202], //Livraison
        ['x' => 65, 'y' => 217], //Prix total HCG
        ['x' => 65, 'y' => 223], //Frais CG
        ['x' => 20, 'y' => 263.5], //Agence
        ['x' => 70, 'y' => 263.5], // Date
        ['x' => 167, 'y' => 183], // Montant arrhes
        ['x' => 128, 'y' => 149], // IBAN
        ['x' => 128, 'y' => 153], // BIC
        ['x' => 130, 'y' => 93.5], // Mise en circulation
        ['x' => 132, 'y' => 269.5], // Agence Nom
        ['x' => 49, 'y' => 43], // Agence Nom
        ['x' => 27, 'y' => 48], // Agence Adresse
        ['x' => 27, 'y' => 53], // Agence CP / Ville
        ['x' => 26, 'y' => 64], // Agence Téléphone
        ['x' => 26, 'y' => 59], // Agence Email
        ['x' => 128, 'y' => 145], // Arrhes
        ['x' => 143, 'y' => 217.5], // CashSentinel
    ];

    // ====== Préparation du PDF ====== //
    $pdf = new \setasign\Fpdi\Fpdi();

    $pageCount = $pdf->setSourceFile('../../documents/reservation_contract.pdf');
    $pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

    $pdf->addPage();
    $pdf->useImportedPage($pageId, 5, 10, 200);

    foreach ($crossToCreate as $index) {
        $pdf->SetFont('Helvetica');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($index['x'], $index['y']);
        $pdf->Write(0, 'X');
    }

    foreach ($importVarPDF as $index => $valPDF) {
        $pdf->SetFont('Helvetica');
        $pdf->SetFontSize(10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($importCoordinates[$index]['x'], $importCoordinates[$index]['y']);
        $valPDF = mb_convert_encoding($valPDF, 'windows-1252', 'UTF-8');
        $pdf->Write(0, $valPDF);
    }

    $folder = "../../storage/reservations/";

    if(!file_exists(filename: $folder)) {
        mkdir($folder, 0777, true);
    }


    // ====== Prépare le chemin pour upload le PDF sur le NextCloud ====== //
    $cleanBrand = cleanValue($resVehicule['vehicules_marque']);
    $cleanModel = cleanValue($resVehicule['vehicules_model']);
    $cleanImmatriculation = cleanValue($resVehicule['vehicules_immatriculation']);
    $cleanNom = cleanValue($resClient['clients_nom']);
    $cleanPrenom = cleanValue($resClient['clients_prenom']);

    $cleanedValueName = $cleanNom . '-' . $cleanPrenom;
    $cleanedValueVehicule = $cleanBrand . '/' . $cleanModel . '-' . $cleanImmatriculation . '/DOCUMENTS_DE_VENTE/CLIENT_ACHETEUR/';

    $pattern = $folder . "BON_RESERVATION_" . $cleanedValueName . "_*.pdf";
    $pdfFiles = glob($pattern);
    $fileCount = count($pdfFiles) + 1;

    $pdfNameFile = "BON_RESERVATION_" . $cleanedValueName . "_" . $fileCount . ".pdf";
    $destinationPath = $folder . $pdfNameFile;

    // ====== Enregistre le PDF sur le serveur et upload le PDF sur le NextCloud dans les documents de vente du client acheteur ====== //
    $pdf->Output('F', $destinationPath);
    // uploadPdfToNextcloud($resAgence['agence_path_client'], $cleanedValueName, $destinationPath);
    uploadPdfToNextcloud($resAgence['agence_path_vehicules'], $cleanedValueVehicule, $destinationPath);
    $pdf->Output('I', $pdfNameFile);
?>