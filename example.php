<?php

require 'vendor/autoload.php';

session_start();

use Onetoweb\Unit4\Client;
use Onetoweb\Unit4\Token;
use Onetoweb\Unit4\Exception\RequestException;

// client parameters
$clientId = 'client_id';
$clientSecret = 'client_secret';
$redirectUrl = 'redirect_url';
$version = 22;
$sandbox = true;

// setup client
$client = new Client($clientId, $clientSecret, $redirectUrl, $version, $sandbox);

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
    echo '<a href="'.$client->getAuthorizationLink().'">Unit4 login</a>';
        
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

// make unit 4 request after token isset
if ($client->getToken()) {
    
    try {
        
        // get product info list
        $productInfoList = $client->getProductInfoList();
        
        // create product
        $product = $client->createProduct([
            'accountId' => '8020',
            'discountAccountId' => '8020',
            'productId' => '42',
            'pricePer' => 3.14,
            'description' => 'test product',
            'shortName' => 'TEST'
        ]);
        
        // get product
        $productId = 'product_id';
        $product = $client->getProduct($productId);
        
        // get customer info list
        $customerInfoList = $client->getCustomerInfoList();
        
        // create customer
        $customer = $client->createCustomer([
            'name' => 'Customer name',
            'shortName' => 'CN',
        ]);
        
        // get customer
        $customerId = 'customer_id';
        $customer = $client->getCustomer($customerId);
        
        // delete customer
        $customerId = 'customer_id';
        $client->deleteCustomer($customerId);
        
        // create order
        $customerId = 'customer_id';
        $productId = 'product_id';
        $order = $client->createOrder([
            'customerId' => $customerId,
            'reference' => 'test order',
            'orderDate' => date('d-m-Y'),
            'paymentConditionId' => '1',
            'orderLines' => [[
                'productId' => $productId,
                'quantityOrdered' => 1,
            ]],
        ]);
        
        // get open orders
        $openOrders = $client->getOrderInfoListOpenOrders();
        
        // get order
        $orderId = 'order_id';
        $order = $client->getOrder($orderId);
        
        // get order line types
        $orderLineTypes = $client->getOrderLineTypeNVL();
        
        // get accounts
        $fiscalYear = 2020;
        $accounts = $client->getAccountInfoList($fiscalYear);
        
        // get account managers
        $accountManagers = $client->getAccountManagerNVL();
        
        // get account category
        $accountCategory = $client->getAccountCategoryNVL();
        
        // get period info list
        $periodInfoList = $client->getPeriodInfoList();
        
        // get payment condition info list
        $paymentConditionInfoList = $client->getPaymentConditionInfoList();
        
        // get document types
        $documentTypes = $client->getDocumentTypeInfoList();
        
        // get document type
        $documentType = 'document_type';
        $documentType = $client->getDocumentTypeInfo($documentType);
        
        // get fiscal year info list
        $fiscalYearInfoList = $client->getFiscalYearInfoList();
        
        // get journal info list
        $journalInfoList = $client->getJournalInfoList();
        
        //get journal transaction info list
        $journalId = 'V';
        $fiscalYear = 2020;
        $journalTransactionInfoList = $client->getJournalTransactionInfoList($journalId, $fiscalYear);
        
        // get journal type nvl
        $journalTypeNVL = $client->getJournalTypeNVL();
        
        // create customer invoice
        $customerInvoice = $client->createCustomerInvoice([
            'customerId' => $customerId,
            'fiscalYear' => 2020,
            'journalId' => 'V',
            'journalTransaction' => 26,
            'paymentConditionId' => '1',
            'periodNumber' => 12,
            'invoiceDate' => '2020-12-01',
        ]);
        
        // get report template configuration list
        $reportTemplateConfigurationList = $client->getReportTemplateConfigurationList();
        
        // get report template configuration
        $configurationId = 'configuration_id';
        $reportTemplateConfiguration = $client->getReportTemplateConfiguration($configurationId);
        
        // get mail message info list
        $mailMessageInfoList = $client->getMailMessageInfoList();
        
        // get mail templates
        $mailTemplates = $client->getMailTemplateList();
        
        // get document invoice by order id
        $orderId = 'order_id';
        $documentInvoice = $client->getDocumentInvoiceByOrderId($orderId, [
            'format' => 2
        ]);
        
    } catch (RequestException $requestException) {
        
        // get json error message
        $errors = json_decode($requestException->getMessage());
        
    }
}