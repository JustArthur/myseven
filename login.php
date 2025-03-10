<?php
    session_start();
    require_once 'database.php';

    if (isset($_COOKIE['user_session']) && isset($_SESSION['user'])) {
        header('Location: index.php');
        exit;
    }
    
    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['connexion'])) {
            $DBB = new ConnexionDB();
            $DB = $DBB->DB();
    
            $valid = true;
    
            $identifiant = htmlspecialchars($identifiant, ENT_QUOTES);
    
            $verif_password = $DB->prepare("SELECT utilisateurs_password FROM utilisateurs WHERE utilisateurs_identifiant = ?");
            $verif_password->execute([$identifiant]);
            $verif_password = $verif_password->fetch();
    
            if ($verif_password && isset($verif_password['utilisateurs_password'])) {
                if (!password_verify($password, $verif_password['utilisateurs_password'])) {
                    $valid = false;
                }
            } else {
                $valid = false;
            }
    
            if ($valid) {
                $sql = $DB->prepare("SELECT * FROM utilisateurs WHERE utilisateurs_identifiant = ?");
                $sql->execute([$identifiant]);
                $sql = $sql->fetch();

                session_regenerate_id(true);
    
                $_SESSION['user'] = array(
                    'id' => htmlspecialchars($sql['utilisateurs_id'], ENT_QUOTES),
                    'identifiant' => htmlspecialchars($sql['utilisateurs_identifiant'], ENT_QUOTES),
                    'agence_id' => htmlspecialchars($sql['utilisateurs_agence_id'], ENT_QUOTES),
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

        <input required type="text" autofocus="true" name="identifiant" placeholder="Identifiant">
        <input required type="password" name="password" placeholder="Mot de passe">

        <input type="submit" name="connexion" value="Se connecter">
    </form>
</body>
</html>