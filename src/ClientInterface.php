<?php

namespace Onetoweb\Multivers;

/**
 * Client Interface.
 */
interface ClientInterface
{
    /**
     * @return string
     */
    public function getBaseUri(): string;
}