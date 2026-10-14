<?php

namespace Onetoweb\Multivers;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Onetoweb\Multivers\Exception\RequestException;
use Onetoweb\Multivers\{Token, ClientInterface};
use DateTime;
use Closure;

/**
 * Multivers Api Abstract Client.
 * 
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb. B.V.
 * 
 * @link https://api.multivers.nl/V21/Help
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * Version
     */
    public const VERSION = 21;
    
    /**
     * @var Token
     */
    private ?Token $token = null;
    
    /**
     * @var callable
     */
    private ?Closure $updateTokenCallback = null;
    
    /**
     * @var string
     */
    protected ?string $database = null;
    
    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     * @param int $version = self::VERSION
     * @param bool $sandbox = false
     */
    public function __construct(
        
        #[\SensitiveParameter]
        private string $clientId,
        
        #[\SensitiveParameter]
        private string $clientSecret,
        
        #[\SensitiveParameter]
        private string $redirectUrl,
        
        protected int $version = self::VERSION,
        
        protected bool $sandbox = false
    ) {
        
    }
    
    /**
     * @param string $database
     * 
     * @return void
     */
    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }
    
    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAuthorizationLink($state = null): string
    {
        $query = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => 'http://UNIT4.Multivers.API/Web/WebApi/*',
            'response_type' => 'code',
        ];
        
        if ($state !== null) {
            $query['state'] = $state;
        }
        
        return $this->getBaseUri().'/OAuth/Authorize?' . http_build_query($query);
    }
    
    /**
     * @param string $code
     * 
     * @return void
     */
    public function requestAccessToken(string $code): void
    {
        $token = $this->post('/OAuth/Token', [
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'authorization_code'
        ], [], false);
        
        $this->updateToken($token);
    }
    
    /**
     * @return void
     */
    private function requestRefreshToken(): void
    {
        $token = $this->post('/OAuth/Token', [
            'refresh_token' => $this->getToken()->getRefreshToken(),
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUrl,
            'grant_type' => 'refresh_token'
        ], [], false);
        
        $this->updateToken($token);
    }
    
    /**
     * @param Closure $updateTokenCallback
     */
    public function setUpdateTokenCallback(Closure $updateTokenCallback): void
    {
        $this->updateTokenCallback = $updateTokenCallback;
    }
    
    /**
     * @param array $tokenArray
     * 
     * @return void
     */
    private function updateToken(array $tokenArray): void
    {
        // get expires
        $expires = new DateTime();
        $expires->setTimestamp(time() + $tokenArray['expires_in']);
        
        $token = new Token($tokenArray['access_token'], $tokenArray['refresh_token'], $expires);
        
        $this->setToken($token);
        
        // token update callback
        ($this->updateTokenCallback)($this->getToken());
    }
    
    /**
     * @param Token $token
     * 
     * @return void
     */
    public function setToken(Token $token): void
    {
        $this->token = $token;
    }
    
    /**
     * @return Token
     */
    public function getToken(): ?Token
    {
        return $this->token;
    }
    
    /**
     * @param string $endpoint
     * @param array $query = []
     *
     * @return mixed
     */
    public function get(string $endpoint, array $query = [], bool $decode = true)
    {
        return $this->request(Method::GET, $endpoint, [], $query, true, $decode);
    }
    
    /**
     * @param string $endpoint
     * @param array $data = []
     * @param array $query = []
     * @param bool $json = true
     * 
     * @return array|null
     */
    public function post(string $endpoint, array $data = [], array $query = [], bool $json = true): ?array
    {
        return $this->request(Method::POST, $endpoint, $data, $query, $json);
    }
    
    /**
     * @param string $endpoint
     * @param array $data = []
     * @param array $query = []
     * @param bool $json = true
     * 
     * @return array|null
     */
    public function put(string $endpoint, array $data = [], array $query = [], bool $json = true): ?array
    {
        return $this->request(Method::PUT, $endpoint, $data, $query, $json);
    }
    
    /**
     * @param string $endpoint
     * @param array $query = []
     * 
     * @return array|null
     */
    public function delete(string $endpoint, array $query = []): ?array
    {
        return $this->request(Method::DELETE, $endpoint, [], $query);
    }
    
    /**
     * @param Method $method
     * @param string $endpoint
     * @param array $data = []
     * @param array $query = []
     * @param bool $json = true
     * 
     * @throws RequestException if the server request contains a error response
     * 
     * @return mixed
     */
    public function request(Method $method, string $endpoint, array $data = [], array $query = [], bool $json = true, bool $decode = true)
    {
        // build request haders
        $headers = [
            'Cache-Control' => 'no-cache',
            'Connection' => 'close',
            'Accept' => 'application/json',
        ];
        
        if ($this->getToken() !== null and $endpoint !== '/OAuth/Token') {
            
            if ($this->getToken()->isExpired()) {
                
                $this->requestRefreshToken();
                
            }
            
            // add bearer token authorization header
            $headers['Authorization'] = "Bearer {$this->getToken()->getAccessToken()}";
            
        }
        
        try {
            
            //  add headers to request options
            $options[RequestOptions::HEADERS] = $headers;
            
            // add post data body
            if (in_array($method, [Method::POST, Method::PUT])) {
                
                if ($json) {
                    $options[RequestOptions::JSON] = $data;
                } else {
                    $options[RequestOptions::FORM_PARAMS] = $data;
                }
                
            }
            
            // build query 
            if (count($query) > 0) {
                $endpoint .= '?' . http_build_query($query);
            }
            
            // build guzzle client
            $guzzleClient = new GuzzleClient([
                RequestOptions::VERIFY => false,
                
            ]);
            
            // build guzzle request
            $result = $guzzleClient->request($method->value, $this->getBaseUri() . $endpoint, $options);
            
            // get contents
            $contents = $result->getBody()->getContents();
            
            // return data
            if ($decode) {
                return json_decode($contents, true);
            } else {
                return $contents;
            }
            
        } catch (GuzzleRequestException|ClientException $guzzleException) {
            
            if ($guzzleException->hasResponse()) {
                
                $contents = $guzzleException->getResponse()->getBody()->getContents();
                
                // check if contents contains json string
                json_decode($contents);
                if (json_last_error() === JSON_ERROR_NONE) {
                    
                    // return error response as exception message
                    throw new RequestException($contents, $guzzleException->getCode(), $guzzleException);
                    
                }
            }
            
            throw $guzzleException;
        }
        
        return null;
    }
}
