<?php

declare(strict_types=1);

namespace Drewlabs\Envoyer\Drivers\Envoyer\Drivers;

trait HasAccessToken
{

    /** @var string */
    private  $accessToken;
    
    /**
     * Override instance access token properties value
     * 
     * **Note** It creates a copy of the instance using
     *          PHP `clone` function instead of modifying existing instance
     * 
     * @param string $apiKey 
     * @param string $apiSecret
     * @return static 
     */
    public function withAccessToken(string $accessToken)
    {
        $self = clone $this;
        $self->accessToken = $accessToken;
        return $self;
    }
}