<?php
    require_once "../../database.php";

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $resImmatriculation = $DB->prepare('SELECT vehicules_immatriculation FROM vehicules ORDER BY vehicules_immatriculation ASC');
    $resImmatriculation->execute();
    $resImmatriculation = $resImmatriculation->fetchAll();

    $tabImmatriculation = array();

    foreach ($resImmatriculation as $immat) {
        $tabImmatriculation[] = $immat['vehicules_immatriculation'];
    }

    echo json_encode($tabImmatriculation);

    $DBB->closeConnection();
?>