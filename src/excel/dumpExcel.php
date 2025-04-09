<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if(empty($_SESSION['user']) || empty($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    }

    require '../../vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    require_once '../../database.php';

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $stmt = $DB->prepare("SELECT * FROM 'vehicules'");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $col = 'A';
    foreach (array_keys($data[0]) as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }

    $row = 2;
    foreach ($data as $entry) {
        $col = 'A';
        foreach ($entry as $value) {
            $sheet->setCellValue($col . $row, $value);
            $col++;
        }
        $row++;
    }

    $filename = "Export_" . date("d-m-Y") . ".xlsx";
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Cache-Control: max-age=0");

    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;
?>