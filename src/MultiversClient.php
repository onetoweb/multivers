<?php

namespace Onetoweb\Multivers;

use Onetoweb\Multivers\AbstractClient;

/**
 * Multivers Api Client.
 *
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb. B.V.
 *
 * @link https://api.multivers.nl/V221/Help
 */
class MultiversClient extends AbstractClient
{
    /**
     * Base Uri.
     */
    const BASE_URI = 'https://%sapi.multivers.nl/V%d';
    
    /**
     * {@inheritdoc}
     */
    public function getBaseUri(): string
    {
        return sprintf(self::BASE_URI, ($this->sandbox ? 'sandbox-' : ''), $this->version);
    }
}