<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();
    
    require_once 'database.php';
    require_once 'src/functions/selectSQL.php';

    $error_message = "";

    if (isset($_COOKIE['user_session']) && isset($_SESSION['user'])) {
        header('Location: index.php');
        exit;
    }
    
    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['connexion'])) {
            $DBB = new ConnexionDB();
            $DBB->openConnection();
    
            $valid = true;
    
            $identifiant = htmlspecialchars($identifiant, ENT_QUOTES);
    
            $verif_password = selectAllUsersInfoWhereId(htmlspecialchars($identifiant, ENT_QUOTES), $DBB->openConnection());
            $verif_password = $verif_password->fetch();
    
            if ($verif_password && isset($verif_password['utilisateurs_password'])) {
                if (!password_verify($password, $verif_password['utilisateurs_password'])) {
                    $valid = false;
                    $error_message = "Identifiant ou mot passe incorect";
                }
            } else {
                $valid = false;
                $error_message = "Identifiant ou mot passe incorect";
            }
    
            if ($valid) {
                $getUser = selectAllUsersInfoWhereId(htmlspecialchars($identifiant, ENT_QUOTES), $DBB->openConnection());
                $getUser = $getUser->fetch();

                session_regenerate_id(true);
    
                $_SESSION['user'] = array(
                    'id' => htmlspecialchars($getUser['utilisateurs_id'], ENT_QUOTES),
                    'identifiant' => htmlspecialchars($getUser['utilisateurs_identifiant'], ENT_QUOTES),
                    'agence_id' => htmlspecialchars($getUser['utilisateurs_agence_id'], ENT_QUOTES),
                );

                setcookie('user_session', $_SESSION['user']['identifiant'], time() + (86400 * 30), "/", "", false, true);
                $DBB->closeConnection();

                header('Location: index.php');
                exit;
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/auth.css">

    <title>Myseven - Se connecter</title>
</head>
<body>

    <div class="logo">
        <img src="assets/img/transakauto-logo.png" alt="Logo Myseven">
    </div>
    <form method="POST">
        <h1>Se connecter</h1>

        <?php if($error_message != "") {echo "<div class='error_message'>" . $error_message . "</div>"; } ?>

        <input required type="text" autofocus="true" name="identifiant" placeholder="Identifiant">
        <input required type="password" name="password" placeholder="Mot de passe">

        <input type="submit" name="connexion" value="Se connecter">
    </form>
</body>
</html>