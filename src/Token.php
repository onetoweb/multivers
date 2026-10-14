<?php

namespace Onetoweb\Multivers;

use DateTime;
use Stringable;

/**
 * Unit4 Api Token
 *
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb B.V.
 */
class Token implements Stringable
{
    /**
     * @param string $accessToken
     * @param string $refreshToken
     * @param DateTime $expires
     */
    public function __construct(
        
        #[\SensitiveParameter]
        private string $accessToken,
        
        #[\SensitiveParameter]
        private string $refreshToken,
        
        private DateTime $expires
    ) {
        
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->accessToken;
    }
    
    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
    
    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
    
    /**
     * @return string
     */
    public function getExpires(): DateTime
    {
        return $this->expires;
    }
    
    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return (bool) (new DateTime() > $this->expires);
    }
}
