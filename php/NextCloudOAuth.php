<?php

class NextCloudOAuth {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $authUrl;
    private $tokenUrl;

    public function __construct($clientId, $clientSecret, $redirectUri) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->authUrl = 'https://nextcloud.myseven.fr/index.php/apps/oauth2/authorize';
        $this->tokenUrl = 'https://nextcloud.myseven.fr/index.php/apps/oauth2/token';
    }

    public function getAuthorizationUrl() {
        return $this->authUrl . '?response_type=code&client_id=' . urlencode($this->clientId) . '&redirect_uri=' . urlencode($this->redirectUri);
    }

    public function getAccessToken($code) {
        $postFields = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
        ];

        $ch = curl_init($this->tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function refreshAccessToken($refreshToken) {
        $postFields = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        $ch = curl_init($this->tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}