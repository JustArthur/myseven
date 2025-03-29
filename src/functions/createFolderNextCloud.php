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

        $baseFolder = trim($baseFolder, '/'); 
        $baseFolder = str_replace(' ', '%20', $baseFolder);
        $baseFolder = mb_convert_encoding($baseFolder, 'UTF-8', 'auto'); 

        $brand = trim($brand, '/'); 
        $brand = str_replace(' ', '%20', $brand);
        $brand = mb_convert_encoding($brand, 'UTF-8', 'auto');

        $brandFolderUrl = rtrim($nextcloudUrl, '/') . '/' . $baseFolder . '/' . $brand . '/';

        var_dump($brandFolderUrl);

        function createFolder($url, $username, $password) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "MKCOL"); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_exec($ch);
    
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return ($httpCode == 201 || $httpCode == 207 || $httpCode == 405);
        }

        if (!createFolder($brandFolderUrl, $username, $password)) {
            return false;
        }

        if (createFolder($brandFolderUrl, $username, $password)) {
            return true;
        } else {
            return false;
        }
    }
?>