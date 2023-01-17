<?php

require 'vendor/autoload.php';

session_start();

use Onetoweb\Multivers\{MultiversClient, BoekhoudgemakClient};
use Onetoweb\Multivers\Token;
use Onetoweb\Multivers\Exception\RequestException;

// client parameters
$clientId = 'client_id';
$clientSecret = 'client_secret';
$redirectUrl = 'redirect_url';
$version = 221;
$sandbox = true;

// setup boekhoudgemak client
$client = new BoekhoudgemakClient($clientId, $clientSecret, $redirectUrl, $version, $sandbox);

// set database
$database = 'database';
$client->setDatabase($database);

// set token callback to store token
$client->setUpdateTokenCallback(function(Token $token) {
    
    $_SESSION['token'] = [
        'accessToken' => $token->getAccessToken(),
        'refreshToken' => $token->getRefreshToken(),
        'expires' => $token->getExpires(),
    ];
});


// load token or request token to gain access to unit 4
if (!isset($_SESSION['token']) and !isset($_GET['code'])) {
    
    // request permission with authorization link
    echo '<a href="'.$client->getAuthorizationLink().'">Multivers login</a>';
        
} else if (!isset($_SESSION['token']) and isset($_GET['code'])) {
    
    // request access token
    $client->requestAccessToken($_GET['code']);
    
} else {
    
    // load token from storage
    $token = new Token(
        $_SESSION['token']['accessToken'],
        $_SESSION['token']['refreshToken'],
        $_SESSION['token']['expires']
    );
    
    $client->setToken($token);
    
}

// make request after token is set
if ($client->getToken()) {
    
    try {
        
        // get administration info list
        $administrationInfoList = $client->get('/api/AdministrationInfoList');
        
        // get product
        $productId = 'product_id';
        $product = $client->get("/api/{$this->getDatabase()}/Product/{$productId}");
        
    } catch (RequestException $requestException) {
        
        // get json error message
        $errors = json_decode($requestException->getMessage());
    }
}