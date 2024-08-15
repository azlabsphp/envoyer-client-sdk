<?php

declare(strict_types=1);

namespace Drewlabs\Envoyer\Drivers\Envoyer\Drivers;

trait HasCallbackUrl
{

    /** @var string|callable */
    private $callback_url;

    /**
     * Copy the current instance and modify the callback url property
     * 
     * @param string|callable $url
     *  
     * @return static 
     */
    public function withCallbackUrl($url)
    {
        $self = clone $this;
        $self->callback_url = $url;
        return $self;
    }
}