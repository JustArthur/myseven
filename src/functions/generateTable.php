<?php
    function generateRows($dataArray, $fields) {
        $items = [];
        foreach ($dataArray as $item) {
            $formattedItem = [];
            foreach ($fields as $key) {
                $value = str_replace(["\n", "\r"], " ", addslashes($item[$key]));
                $formattedItem[] = "$key: \"$value\"";
            }
            $items[] = "{" . implode(", ", $formattedItem) . "}";
        }
        return implode(",\n", $items);
    }

    $customerFields = [
        'clients_nom',
        'clients_prenom',
        'clients_email',
        'clients_telephone',
        'clients_rue',
        'clients_ville',
        'clients_cp',
        'clients_numero_cni',
        'clients_agence_id'
    ];

    $vehicleFields = [
        'vehicules_immatriculation',
        'vehicules_marque',
        'vehicules_model',
        'vehicules_annee',
        'vehicules_puissance',
        'vehicules_type_boite',
        'vehicules_couleur',
        'vehicules_kilometrage',
        'vehicules_agence_id'
    ];

?>