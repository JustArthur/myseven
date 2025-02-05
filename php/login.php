<?php
    // session_start();
    // include_once '../connexionDB.php';

    // if (!empty($_SESSION['user'])) {
    //     header('Location: ../index.php');
    //     exit();
    // }
    
    // if (!empty($_POST)) {
    //     extract($_POST);
    //     if (isset($_POST['connexion'])) {
    //         $DBB = new ConnexionDB();
    //         $DB = $DBB->DB();
    
    //         $valid = true;
    
    //         $identifiant = htmlspecialchars($identifiant, ENT_QUOTES);
    
    //         $verif_password = $DB->prepare("SELECT passwordUser FROM testtablelogin WHERE identifiantUser = ?");
    //         $verif_password->execute([$identifiant]);
    //         $verif_password = $verif_password->fetch();
    
    //         if ($verif_password && isset($verif_password['passwordUser'])) {
    //             if (!password_verify($password, $verif_password['passwordUser'])) {
    //                 $valid = false;
    //             }
    //         } else {
    //             $valid = false;
    //         }
    
    //         if ($valid) {
    //             $sql = $DB->prepare("SELECT * FROM testtablelogin WHERE identifiantUser = ?");
    //             $sql->execute([$identifiant]);
    //             $sql = $sql->fetch();

    //             session_regenerate_id(true);
    
    //             $_SESSION['user'] = array(
    //                 'id' => htmlspecialchars($sql['idUser'], ENT_QUOTES),
    //                 'identifiant' => htmlspecialchars($sql['identifiantUser'], ENT_QUOTES)
    //             );

    //             setcookie('user_session', $_SESSION['user']['identifiant'], time() + (86400 * 30), "/", "", false, true);
    

    //             header('Location: ../index.php');
    //             exit();
    //         }
    //     }
    // }

    require_once '../vendor/autoload.php';
    use Dotenv\Dotenv;

    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    $nextcloudUrl = $_ENV['NEXT_CLOUD_URL'];
    $clientId = $_ENV['NEXT_CLOUD_CLIENT_ID'];
    $redirectUri = $_ENV['NEXT_CLOUD_REDIRECT_URI'];
    $scope = "openid profile email";

    $authorizeUrl = "$nextcloudUrl/apps/oauth2/authorize?response_type=code&client_id=$clientId&redirect_uri=$redirectUri&scope=$scope";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../style/connexionStyle.css">

    <title>Se connecter</title>
</head>
<body>
    <a href="<?= htmlspecialchars($authorizeUrl) ?>">
        <button>Se connecter avec Nextcloud</button>
    </a>
</body>
</html>