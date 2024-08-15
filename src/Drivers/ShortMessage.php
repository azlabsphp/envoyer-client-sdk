<?php

namespace Drewlabs\Envoyer\Drivers\Envoyer\Drivers;

use Drewlabs\Envoyer\Contracts\ClientInterface;
use Drewlabs\Envoyer\Contracts\NotificationInterface;
use Drewlabs\Envoyer\Contracts\NotificationResult;
use Drewlabs\Envoyer\Drivers\Envoyer\Exceptions\AuthorizationException;
use Drewlabs\Envoyer\Drivers\Envoyer\Exceptions\RequestException;
use Drewlabs\Envoyer\Drivers\Envoyer\Result;
use Drewlabs\Psr18\Client;
use Drewlabs\Psr7\Request;

final class ShortMessage implements ClientInterface
{
    use HasAccessToken;
    use HasCallbackUrl;

    /** @var string */
    private $endpoint;

    /**
     * Creates new NGHCorp envoyer driver instance
     * 
     * @param string $endpoint 
     * @param string|null $accessToken 
     * @param null|string|callable $callback_url
     */
    public function __construct(string $endpoint, string $accessToken = null, $callback_url = null)
    {
        $this->endpoint = $endpoint;
        $this->accessToken = $accessToken;
        $this->callback_url = $callback_url;
    }


    /**
     * Creates new `NGHCorp` envoyer driver instance
     * 
     * **Note** If the callback_url is a function the function is
     *          invoked on the notification instance as parameter.
     * 
     * @param string $endpoint 
     * @param string|null $accessToken 
     * @param null|string|callable $callback_url
     * 
     * @return static 
     */
    public static function new(string $endpoint, string $accessToken = null, $callback_url = null)
    {
        return new static($endpoint, $accessToken, $callback_url);
    }


    public function sendRequest(NotificationInterface $instance): NotificationResult
    {
        if (is_null($this->accessToken)) {
            throw new AuthorizationException("Access token not provided. Please call the withAccessToken() to provide authorization credentials.");
        }
        $response = Client::new(null, [
            'base_url' => rtrim($this->endpoint, '/'),
            'connect_timeout' => 5,
            'verify' => false,
            'force_resolve_ip' => false,
            'request' => [
                'body' => [
                    'from' => $instance->getSender()->__toString(),
                    'to' => $instance->getReceiver()->__toString(),
                    'content' => strval($instance->getContent()),
                    //'reference' => $instance->id() ?? uniqid(time()),
                    'callback_url' => is_callable($this->callback_url) ? call_user_func($this->callback_url, $instance) : $this->callback_url,
                ],
                'headers' => [
                    'Authorization' => sprintf("ACCESS_TOKEN %s", $this->accessToken),
                    'Accept' => 'application/json'
                ],
                'timeout' => 60
            ]
        ])->json()->sendRequest(new Request('POST', 'api/messages'));

        if (($statusCode  = $response->getStatusCode()) && (200 > $statusCode || 204 < $statusCode)) {
            throw new RequestException(sprintf("/POST /api/send-sms fails with status %d -  %s", $statusCode, $response->getBody()));
        }

        return Result::fromJson(json_decode($response->getBody(), true));
    }
}
