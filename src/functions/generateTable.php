<?php
    function generateRows($dataArray, $fields) {
        $items = [];
        foreach ($dataArray as $item) {
            $formattedItem = [];
            foreach ($fields as $key) {
                $value = str_replace(["\n", "\r"], " ", addslashes($item[$key]));
                
                if ($key === 'clients_copie_cni' || $key === 'vehicules_carte_grise') {
                    $mimeType = finfo_buffer(finfo_open(), $item[$key], FILEINFO_MIME_TYPE);
                    
                    if ($mimeType === 'application/pdf') {
                        $value = base64_encode($item[$key]);
                        $value = 'data:application/pdf;base64,' . $value;
                    } else {
                        $value = base64_encode($item[$key]);
                        $value = 'data:image/jpeg;base64,' . $value;
                    }
                }                
                
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

    $allCustomersField = [
        'clients_nom',
        'clients_prenom',
        'clients_email',
        'clients_telephone',
        'clients_anniversaire',
        'clients_lieu_naissance',
        'clients_type',
        'clients_rue',
        'clients_ville',
        'clients_cp',
        'clients_numero_cni',
        'clients_copie_cni',
        'clients_id'
    ];

    $allVehicleFields = [
        'vehicules_immatriculation',
        'vehicules_marque',
        'vehicules_model',
        'vehicules_annee',
        'vehicules_puissance',
        'vehicules_type_boite',
        'vehicules_couleur',
        'vehicules_finition',
        'vehicules_date_mise_en_circu',
        'vehicules_date_entretien',
        'vehicules_frais_prevoir',
        'vehicules_frais_recent',
        'vehicules_type',
        'vehicules_numero_serie',
        'vehicules_origine',
        'vehicules_kilometrage',
        'vehicules_nombre_main',
        'vehicules_carte_grise',
        'vehicules_id',
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