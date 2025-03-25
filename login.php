<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    session_start();
    
    require_once 'database.php';
    require_once 'src/functions/selectSQL.php';

    $DBB = new ConnexionDB();

    $error_message = [];

    if (isset($_SESSION['user']) && !isset($_COOKIE['user_session'])) {
        setcookie('user_session', $_SESSION['user']['identifiant'], time() + (86400 * 30), "/", "", false, true);
    } elseif (isset($_COOKIE['user_session']) && !isset($_SESSION['user'])) {
        $identifiant = $_COOKIE['user_session'];

        $DB = $DBB->openConnection();
        $user = selectAllUsersInfoWhereId($identifiant, $DB);
        $user = $user->fetch();
        
        if ($user) {
            $_SESSION['user'] = [
                'id' => htmlspecialchars($user['utilisateurs_id'], ENT_QUOTES),
                'identifiant' => htmlspecialchars($user['utilisateurs_identifiant'], ENT_QUOTES),
                'agence_id' => htmlspecialchars($user['utilisateurs_agence_id'], ENT_QUOTES),
                'role' => htmlspecialchars($user['utilisateurs_role'], ENT_QUOTES)
            ];
        } else {
            session_destroy();
        }
    }
    
    if (!empty($_POST)) {
        extract(array: $_POST);
        if (isset($_POST['connexion'])) {
            
            $DBB->openConnection();
    
            $valid = true;
    
            $identifiant = htmlspecialchars($identifiant, ENT_QUOTES);
    
            $verif_password = selectAllUsersInfoWhereId(htmlspecialchars($identifiant, ENT_QUOTES), $DBB->openConnection());
            $verif_password = $verif_password->fetch();
    
            if ($verif_password && isset($verif_password['utilisateurs_password'])) {
                if (!password_verify($password, $verif_password['utilisateurs_password'])) {
                    $valid = false;
                    $error_message = [
                        'type' => 'error',
                        'message' => 'Identifiant ou mot passe incorect'
                    ];
                }
            } else {
                $valid = false;
                $error_message = [
                    'type' => 'error',
                    'message' => 'Identifiant ou mot passe incorect'
                ];
            }
    
            if ($valid) {
                $getUser = selectAllUsersInfoWhereId(htmlspecialchars($identifiant, ENT_QUOTES), $DBB->openConnection());
                $getUser = $getUser->fetch();

                session_regenerate_id(true);
    
                $_SESSION['user'] = [
                    'id' => htmlspecialchars($getUser['utilisateurs_id'], ENT_QUOTES),
                    'identifiant' => htmlspecialchars($getUser['utilisateurs_identifiant'], ENT_QUOTES),
                    'agence_id' => htmlspecialchars($getUser['utilisateurs_agence_id'], ENT_QUOTES),
                    'role' => htmlspecialchars($getUser['utilisateurs_role'], ENT_QUOTES)
                ];

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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>

    <link rel="stylesheet" href="assets/css/auth.css">

    <title>Myseven - Se connecter</title>
</head>
<body>

    <div class="logo">
        <img src="assets/img/transakauto-logo.png" alt="Logo Myseven">
    </div>
    <form method="POST">
        <?php if(!empty($error_message)) {echo "<div class='error_message " . $error_message['type'] . "'>" . $error_message['message'] . "</div>"; } ?>

        <div class="input_box">
            <span class="label form_required">Identifiant</span>
            <input required type="text" name="identifiant" id="identifiant">
        </div>

        <div class="input_box">
            <span class="label form_required">Mot de passe</span>
            <input required type="password" name="password" id="password">
            <span onclick="showPassword()" id="showPassword" class="material-symbols-outlined icon">visibility</span>
        </div>

        <input type="submit" name="connexion" value="Se connecter">
    </form>

    <script src="assets/js/showPassword.js"></script>
</body>
</html>