<?php

namespace Drewlabs\Envoyer\Drivers\Envoyer\Drivers;

use Drewlabs\Envoyer\Contracts\AttachmentsAware;
use Drewlabs\Envoyer\Contracts\ClientInterface;
use Drewlabs\Envoyer\Contracts\NotificationInterface;
use Drewlabs\Envoyer\Contracts\NotificationResult;
use Drewlabs\Envoyer\Drivers\Envoyer\Exceptions\AuthorizationException;
use Drewlabs\Envoyer\Drivers\Envoyer\Exceptions\RequestException;
use Drewlabs\Envoyer\Drivers\Envoyer\Result;
use Drewlabs\Psr18\Client;
use Drewlabs\Psr7\Request;

final class Mail implements ClientInterface
{
    use HasAccessToken;
    use HasCallbackUrl;

    /** @var string */
    private  $endpoint;

    /**
     * Creates new envoyer mail driver instance
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
     * Creates new envoyer mail driver instance
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

        $callback = is_callable($this->callback_url) ? call_user_func($this->callback_url, $instance) : $this->callback_url;

        $response = Client::new(null, [
            'base_url' => rtrim($this->endpoint, '/'),
            'connect_timeout' => 5,
            'verify' => false,
            'force_resolve_ip' => false,
            'request' => [
                'body' => [
                    ['name' => 'from', 'contents' =>  $instance->getSender()->__toString()],
                    ['name' => 'to', 'contents' => $instance->getReceiver()->__toString()],
                    ['name' => 'content', 'contents' => strval($instance->getContent())],
                    // ['name' => 'reference', 'contents' => $instance->id() ?? uniqid(time())],
                    ['name' => 'callback_url', 'contents' => $callback],
                    [
                        'name' => 'attachments',
                        'contents' => $instance instanceof AttachmentsAware ? $instance->getAttachments() : []
                    ]
                ],
                'headers' => [
                    'Authorization' => sprintf("ACCESS_TOKEN %s", $this->accessToken),
                    'Accept' => 'application/json'
                ],
                'timeout' => 30
            ]
        ])->multipart()->sendRequest(new Request('POST', '/api/mails'));

        if (($statusCode  = $response->getStatusCode()) && (200 > $statusCode || 204 < $statusCode)) {
            throw new RequestException(sprintf("/POST /api/send-sms fails with status %d -  %s", $statusCode, $response->getBody()));
        }

        return Result::fromJson(json_decode($response->getBody(), true));
    }
}
