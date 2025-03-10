<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();

    if (!isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        header('Location: ../../login.php');
        exit();
    }

    require_once '../../database.php';

    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['submit_btn'])) {

            if (empty($immatriculation) || empty($brand) || empty($model) || empty($puissance) || empty($type_boite) || empty($color) || empty($finition) || empty($kilometrage) || empty($annee) || empty($date_entretien) || empty($frais_prevoir) || empty($frais_recent)) {
                echo '
                    <script>
                        window.alert("Tous les champs sont obligatoires.");
                    </script>
                ';
            }

            $DBB = new ConnexionDB();
            $DB = $DBB->DB();

            switch($type_boite) {
                case 1:
                    $type_boite_value = "Manuelle";
                    break;

                case 2:
                    $type_boite_value = "Automatique";
                    break;

                default:
                    $type_boite_value = "null";
                    break;
            }

            $getImmat = $DB->prepare("SELECT vehicules_immatriculation FROM vehicules WHERE vehicules_immatriculation = ?");
            $getImmat->execute([$immatriculation]);
            $getImmat = $getImmat->fetch();

            if (!$getImmat) {
                $stmt = $DB->prepare("INSERT INTO vehicules (vehicules_marque, vehicules_model, vehicules_immatriculation, vehicules_puissance, vehicules_type_boite, vehicules_couleur, vehicules_finition, vehicules_kilometrage, vehicules_annee, vehicules_date_entretien, vehicules_frais_prevoir, vehicules_frais_recent, vehicules_agence_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->execute([$brand, $model, $immatriculation, $puissance, $type_boite_value, $color, $finition, $kilometrage, $annee, $date_entretien, $frais_prevoir, $frais_recent , intval($_SESSION['user']["agence_id"])]);
                        
                echo '
                        <div class="pop_up">
                            <div class="pop_content">
                                <h1>Le véhicule à bien été créer</h1>

                                <div class="input_btn">
                                    <a href="../../index.php" class="btn yes">Retournez au menu</a>
                                </div>
                            </div>
                        </div>
                        <script>
                            document.getElementById("body").style.overflow = "hidden";
                        </script>
                        ';
            } else {
                echo '
                    <script>
                        window.alert("L\'immatriculation est déjà utilisé");
                    </script>
                ';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets/css/pop_up.css">

    <title>Myseven - Créer un véhicule</title>
</head>

<body id='body'>
    <main>

        <div class="search-container">
            <h2>Créer un véhicule</h2>
            <form class="gap" id="form_pdf" method="POST" enctype="multipart/form-data">
                <div class="input_box">
                    <span class="label form_required">Immatriculation</span>
                    <input required name="immatriculation" type="text" id="immatriculation">
                </div>

                <div class="input_box">
                    <span class="label form_required">Marque</span>
                    <input required name="brand" type="text" id="brand">
                </div>

                <div class="input_box">
                    <span class="label form_required">Modèle</span>
                    <input required name="model" type="text" id="model">
                </div>

                <div class="input_box">
                    <span class="label form_required">Puissance</span>
                    <input required name="puissance" type="number" id="puissance">
                </div>

                <div class="input_box">
                    <select required name="type_boite" id="type_boite">
                        <option value=0>-- Choisir le type de boite --</option>
                        <option value=1>Manuelle</option>
                        <option value=2>Automatique</option>
                    </select>
                </div>

                <div class="input_box">
                    <span class="label form_required">Couleur du véhicule</span>
                    <input required type="text" id="color" name="color">
                </div>

                <div class="input_box">
                    <span class="label form_required">Finition du véhicule</span>
                    <input required type="text" id="finition" name="finition">
                </div>

                <div class="input_box">
                    <span class="label form_required">Kilometrage</span>
                    <input required type="number" id="kilometrage" name="kilometrage">
                </div>

                <div class="input_box">
                    <span class="label form_required">Année du véhicule</span>
                    <input required type="number" id="annee" name="annee">
                </div>

                <div class="input_box">
                    <span class="label form_required">Date entretien</span>
                    <input required type="date" id="date_entretien" name="date_entretien">
                </div>

                <div class="input_box">
                    <span class="label form_required">Frais à prévoir</span>
                    <input required type="number" id="frais_prevoir" name="frais_prevoir">
                </div>

                <div class="input_box">
                    <span class="label form_required">Frais récent</span>
                    <input required type="number" id="frais_recent" name="frais_recent">
                </div>

                <div class="input_box">
                    <input class="submit_btn" value="Créer le véhicule" type="submit" name="submit_btn" id="submit_btn">
                </div>
            </form>

        </div>
    </main>
</body>

</html>