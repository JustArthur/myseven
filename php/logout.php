<?php
    session_start();

    $_SESSION['user'] = array();

    if (ini_get(option: "session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(name: session_name(), value: '', expires_or_options: time() - 42000,
            path: $params["path"], domain: $params["domain"],
            secure: $params["secure"], httponly: $params["httponly"]
        );
    }
    
    if (isset($_COOKIE['user_session'])) {
        setcookie(name: 'user_session', value: '', expires_or_options: time() - 42000, path: '/');
    }
    
    session_destroy();

    header(header: 'Location: ./login.php');
    exit();
?>