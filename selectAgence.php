<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();
    
    require_once 'database.php';
    require_once 'src/functions/selectSQL.php';

    if(empty($_SESSION['user'])) {
        header('Location: login.php');
    }

    if(isset($_SESSION['user']['agence_id'])) {
        header('Location: ./');
        exit();
    }

    $DBB = new ConnexionDB();
    $DB = $DBB->openConnection();

    $getAgence = $DB->prepare('SELECT * FROM agence');
    $getAgence->execute();
    $getAgence = $getAgence->fetchAll();

    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['connexion'])) {
            $getUser = selectAllUsersInfoWhereId(htmlspecialchars($_SESSION['user']['identifiant'], ENT_QUOTES), $DB);
            $getUser = $getUser->fetch();
        
            $_SESSION['user']['agence_id'] = htmlspecialchars($choiceAgence, ENT_QUOTES);
    
            setcookie('user_session', $_SESSION['user']['identifiant'], time() + (86400 * 30), "/", "", false, true);
            $DBB->closeConnection();
    
            header('Location: ./');
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>

    <link rel="stylesheet" href="assets/css/auth.css">

    <title>Myseven - Se connecter</title>
</head>
<body>

    <div class="logo">
        <img src="assets/img/transakauto-logo.png" alt="Logo Myseven">
    </div>
    <form method="POST">
        <div class="input_box">
            <span class="label form_required">Choisir l'agence</span>
            <select required name="choiceAgence" id="choiceAgence">
                <?php foreach($getAgence as $agence) {
                    echo '<option value="' . $agence['agence_id'] . '">' . $agence['agence_nom'] . '</option>';
                } ?>
            </select>

            <p class="text_error hidden">Ce champ est requis</p>
        </div>

        <input type="submit" name="connexion" value="Choisir cette agence">
    </form>

    <script src="assets/js/showPassword.js"></script>
</body>
</html>