<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require_once('vendor/setasign/fpdf/fpdf.php');
    require_once('vendor/setasign/fpdi/src/autoload.php');

    require_once( "connexionDB.php");

    $DBB = new ConnexionDB();
    $DB = $DBB->DB();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="/style/style.css">

    <title>Document</title>
</head>
<body>
    
</body>
</html>