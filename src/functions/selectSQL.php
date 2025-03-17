<?php
    function selectAllUsersInfoWhereId($userId, $DB) {
        $sql = $DB->prepare('SELECT * FROM utilisateurs WHERE utilisateurs_identifiant = ?');
        $sql->execute([$userId]);
        
        return $sql;
    }

    function selectAllClient($DB) {
        $sql = $DB->prepare('SELECT * FROM clients ORDER BY clients_nom ASC');
        $sql->execute();

        return $sql;
    }

    function selectAllClientWhereAgence($userAgenceId, $DB) {
        $sql = $DB->prepare('SELECT * FROM clients WHERE clients_agence_id = ? ORDER BY clients_nom ASC');
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