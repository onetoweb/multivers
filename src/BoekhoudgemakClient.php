<?php

namespace Onetoweb\Multivers;

use Onetoweb\Multivers\AbstractClient;

/**
 * Boekhoudgemak Api Client.
 * 
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb. B.V.
 * 
 * @link https://api.boekhoudgemak.nl/V221/Help
 */
class BoekhoudgemakClient extends AbstractClient
{
    /**
     * Base Uri.
     */
    const BASE_URI = 'https://%sapi.boekhoudgemak.nl/V%d';
    
    /**
     * {@inheritdoc}
     */
    public function getBaseUri(): string
    {
        return sprintf(self::BASE_URI, ($this->sandbox ? 'sandbox-' : ''), $this->version);
    }
}