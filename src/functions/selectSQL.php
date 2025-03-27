<?php

    function selectAllAgence($DB) {
        $sql = $DB->prepare('SELECT * FROM agence ORDER BY agence_nom ASC');
        $sql->execute();

        return $sql;
    }

    function selectAllUsersInfoWhereId($userIdendifiant, $DB) {
        $sql = $DB->prepare('SELECT * FROM utilisateurs WHERE utilisateurs_identifiant = ?');
        $sql->execute([$userIdendifiant]);
        
        return $sql;
    }

    function selectAllClientVendeur($DB) {
        $sql = $DB->prepare('SELECT * FROM clients WHERE clients_type = "Vendeur" ORDER BY clients_nom ASC');
        $sql->execute();

        return $sql;
    }

    function selectAllClientAcheteur($DB) {
        $sql = $DB->prepare('SELECT * FROM clients WHERE clients_type = "Acheteur" ORDER BY clients_nom ASC');
        $sql->execute();

        return $sql;
    }

    function selectAllClientVendeurWhereAgence($userAgenceId, $DB) {
        $sql = $DB->prepare('SELECT * FROM clients WHERE clients_agence_id = ? AND clients_type = "Vendeur" ORDER BY clients_nom ASC');
        $sql->execute([$userAgenceId]);

        return $sql;
    }

    function selectAllClientAcheteurWhereAgence($userAgenceId, $DB) {
        $sql = $DB->prepare('SELECT * FROM clients WHERE clients_agence_id = ? AND clients_type = "Acheteur" ORDER BY clients_nom ASC');
        $sql->execute([$userAgenceId]);

        return $sql;
    }

    function selectAllVehicle($DB) {
        $sql = $DB->prepare('SELECT * FROM vehicules ORDER BY vehicules_immatriculation ASC');
        $sql->execute();

        return $sql;
    }


    function selectAllVehicleWhereAgence($userAgenceId, $DB) {
        $sql = $DB->prepare('SELECT * FROM vehicules WHERE vehicules_agence_id = ? ORDER BY vehicules_immatriculation ASC');
        $sql->execute([$userAgenceId]);

        return $sql;
    }
?>