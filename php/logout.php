<?php
    session_start();

    $_SESSION['user'] = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    if (isset($_COOKIE['user_session'])) {
        setcookie('user_session', '', time() - 42000, '/');
    }
    
    session_destroy();

    header('Location: ../index.php');
    exit();
?>