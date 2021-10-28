<?php


namespace Ymlluo\Notify;


use GuzzleHttp\Client;
use Ymlluo\GroupRobot\GroupRobot;

class BaseNotify
{
    public $webhook;

    public $message;

    public $httpClient;


    /**
     * set webhook url
     *
     * @param string $webhook
     */
    public function setWebhook(string $webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * raw message
     *
     * @param array $data
     */
    public function raw(array $data)
    {
        $this->message = $data;
    }

    /**
     * send message
     *
     * @return mixed|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send()
    {
        if (!isset($this->message)) {
            throw new \Exception('message not set!');
        }
        if (!$this->webhook) {
            throw new \Exception('webhook not set');
        }
        $response = $this->httpClient()->post($this->webhook, ['json'=>$this->message,'http_errors'=>false]);
        if ($response->getStatusCode() === 200){
            return json_decode($response->getBody(),true);
        }
        return $response->getBody();
    }

    /**
     * Http client
     *
     * @return Client
     */
    public function httpClient()
    {
        $this->httpClient = new Client();
        return $this->httpClient;
    }


}