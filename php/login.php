<?php
    session_start();
    include_once '../connexionDB.php';

    if (!empty($_SESSION['user'])) {
        header(header: 'Location: ../index.php');
        exit();
    }
    
    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['connexion'])) {
            $DBB = new ConnexionDB();
            $DB = $DBB->DB();
    
            $valid = true;
    
            $identifiant = htmlspecialchars(string: $identifiant, flags: ENT_QUOTES);
    
            $verif_password = $DB->prepare(query: "SELECT passwordUser FROM users WHERE identifiantUser = ?");
            $verif_password->execute(params: [$identifiant]);
            $verif_password = $verif_password->fetch();
    
            if ($verif_password && isset($verif_password['passwordUser'])) {
                if (!password_verify(password: $password, hash: $verif_password['passwordUser'])) {
                    $valid = false;
                }
            } else {
                $valid = false;
            }
    
            if ($valid) {
                $sql = $DB->prepare(query: "SELECT * FROM users WHERE identifiantUser = ?");
                $sql->execute(params: [$identifiant]);
                $sql = $sql->fetch();

                session_regenerate_id(delete_old_session: true);
    
                $_SESSION['user'] = array(
                    'id' => htmlspecialchars(string: $sql['idUser'], flags: ENT_QUOTES),
                    'identifiant' => htmlspecialchars(string: $sql['identifiantUser'], flags: ENT_QUOTES)
                );

                setcookie(name: 'user_session', value: $_SESSION['user']['identifiant'], expires_or_options: time() + (86400 * 30), path: "/", domain: "", secure: false, httponly: true);
                $DBB->closeConnection();

                header(header: 'Location: ../index.php');
                exit();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../style/connexionStyle.css">

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