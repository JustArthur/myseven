<?php
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require_once '../../vendor/autoload.php';
    use Dotenv\Dotenv;

    // Charger les variables d'environnement une seule fois
    $dotenv = Dotenv::createImmutable("../../");
    $dotenv->load();

    $nextcloudUrl = $_ENV['NEXT_CLOUD_URL'];
    $username = $_ENV['NEXT_CLOUD_USER'];
    $password = $_ENV['NEXT_CLOUD_PASSWORD'];

    // Vérifier si la fonction existe avant de la déclarer
    if (!function_exists('createFolder')) {
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
    }

    function createNextcloudFolder($baseFolder, $brand) {
        global $nextcloudUrl, $username, $password;

        $baseFolder = trim($baseFolder, '/'); 
        $baseFolder = str_replace(' ', '%20', $baseFolder);
        $baseFolder = mb_convert_encoding($baseFolder, 'UTF-8', 'auto'); 

        $brand = trim($brand, '/'); 
        $brand = str_replace(' ', '%20', $brand);
        $brand = mb_convert_encoding($brand, 'UTF-8', 'auto');

        $brandFolderUrl = rtrim($nextcloudUrl, '/') . '/' . $baseFolder . '/' . $brand . '/';

        return createFolder($brandFolderUrl, $username, $password);
    }

    function uploadPdfToNextcloud($baseFolder, $brand, $filePath) {
        global $nextcloudUrl, $username, $password;

        $baseFolder = trim($baseFolder, '/'); 
        $baseFolder = str_replace(' ', '%20', $baseFolder);
        $baseFolder = mb_convert_encoding($baseFolder, 'UTF-8', 'auto'); 

        $brand = trim($brand, '/'); 
        $brand = str_replace(' ', '%20', $brand);
        $brand = mb_convert_encoding($brand, 'UTF-8', 'auto');

        $fileName = basename($filePath);
        $fileUrl = rtrim($nextcloudUrl, '/') . '/' . $baseFolder . '/' . $brand . '/' . $fileName;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fileUrl);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $fileHandle = fopen($filePath, 'r');
        curl_setopt($ch, CURLOPT_INFILE, $fileHandle);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filePath));

        curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        fclose($fileHandle);
        
        $fileHandle = fopen($filePath, 'r');
        if (!$fileHandle) {
            die("Erreur : Impossible d'ouvrir le fichier $filePath");
        }

        curl_setopt($ch, CURLOPT_INFILE, $fileHandle);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filePath));

        $response = curl_exec($ch);
        
        if ($response === false) {
            die("Erreur cURL : " . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fileHandle);

        
        return ($httpCode == 201 || $httpCode == 207 || $httpCode == 405);
    }
?>