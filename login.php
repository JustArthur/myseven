<?php
    session_start();
    require_once 'database.php';

    if (!empty($_SESSION['user'])) {
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
    
            $verif_password = $DB->prepare("SELECT passwordUser FROM users WHERE identifiantUser = ?");
            $verif_password->execute([$identifiant]);
            $verif_password = $verif_password->fetch();
    
            if ($verif_password && isset($verif_password['passwordUser'])) {
                if (!password_verify($password, $verif_password['passwordUser'])) {
                    $valid = false;
                }
            } else {
                $valid = false;
            }
    
            if ($valid) {
                $sql = $DB->prepare("SELECT * FROM users WHERE identifiantUser = ?");
                $sql->execute([$identifiant]);
                $sql = $sql->fetch();

                session_regenerate_id(true);
    
                $_SESSION['user'] = array(
                    'id' => htmlspecialchars($sql['idUser'], ENT_QUOTES),
                    'identifiant' => htmlspecialchars($sql['identifiantUser'], ENT_QUOTES)
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
    <form method="POST">
        <h1>Se connecter</h1>

        <input required type="text" name="identifiant" placeholder="Identifiant">
        <input required type="password" name="password" placeholder="Mot de passe">

        <input type="submit" name="connexion" value="Se connecter">
    </form>
</body>
</html>