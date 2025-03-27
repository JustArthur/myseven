<?php

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require_once '../../vendor/autoload.php';
    use Dotenv\Dotenv;

    function createNextcloudFolder($baseFolder, $brand) {
        $dotenv = Dotenv::createImmutable("../../");
        $dotenv->load();

        $nextcloudUrl = $_ENV['NEXT_CLOUD_URL'];
        $username = $_ENV['NEXT_CLOUD_USER'];
        $password = $_ENV['NEXT_CLOUD_PASSWORD'];

        $brandFolderUrl = $nextcloudUrl . $baseFolder . urlencode($brand) . "/";

        function createFolder($url, $username, $password) {
            $ch = curl_init();
            // curl_setopt($ch, CURLOPT_URL, $url);
            // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "MKCOL"); 
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
            // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            // curl_exec($ch);
    
            // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // curl_close($ch);

            $httpCode = 201;

            return ($httpCode == 201 || $httpCode == 207);
        }

        if (!createFolder($brandFolderUrl, $username, $password)) {
            return "Erreur 1";
        }

        if (createFolder($brandFolderUrl, $username, $password)) {
            return "Dossier créé";
        } else {
            return "Erreur 2";
        }
    }
?>