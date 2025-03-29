<?php
    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['submit_btn'])) {

            if(empty($immatCar)) {
                $_POST['client_email'] = $client;
                $error_message = [
                    'type' => 'error',
                    'message' => 'Aucune immatriculation selectionné.'
                ];
            } else {
                echo '
                <form id="redirectForm" action="reservationForm.php" method="POST">
                    <input type="hidden" name="client" value="' . strtolower($client) .'">
                    <input type="hidden" name="immatCar" value="' . $immatCar .'">
                </form>
                <script>
                    document.getElementById("redirectForm").submit();
                </script>
            ';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <link rel="stylesheet" href="../../assets/css/forms.css">

    <title>Myseven - Selectionner un véhicule</title>
</head>

<body id="body">
    <main>

        <div class="search-container">
            <h2>Selectionner un véhicule</h2>
            <form id="form_pdf" method="POST">
                <?php if(!empty($error_message)) {echo "<div style='margin-bottom: 30px;' class='error_message " . $error_message['type'] . "'>" . $error_message['message'] . "</div>"; } ?>

                <input type="hidden" name="client" value="<?= $_POST['client_email'] ?>">

                <div class="wrapper marque">
                    <span class="label form_required">Immatriculation du véhicule</span>
                    <div class="select-btn">
                        <span class="select">Selectionner une immatriculation...</span>
                        <i class="uil uil-angle-down"></i>
                    </div>
                    <div class="content">
                        <div class="search">
                            <input type="text" placeholder="Rechercher une immatriculation...">
                        </div>
                        <ul id="carSelect" class="options"></ul>
                    </div>
                </div>

                <input type="hidden" name="immatCar" id="marque_id">

                <div class="input_box">
                    <input class="submit_btn" type="submit" name="submit_btn" id="submit_btn" value="Selectionner ce véhicule">
                </div>
            </form>
        </div>
    </main>

    <script src="../../assets/js/errorMessages.js"></script>
    <script src="../../assets/js/wrapperSelectImmatriculation.js"></script>
</body>

</html>