<?php


namespace ymlluo\Notify;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class BaseNotify
{
    public $webhook;

    public $message;

    public $httpClient;

    public function setWebhook(string $webhook)
    {
        $this->webhook = $webhook;
    }



    public function send()
    {
        if (!isset($this->message)) {
            throw new \Exception('message not set!');
        }
        if (!$this->webhook) {
            throw new \Exception('webhook not set');
        }
        $response = $this->httpClient()->post($this->webhook, $this->message);
        return $response->getBody();
    }

    public function httpClient()
    {
        $this->httpClient = new Client();
        return $this->httpClient;
    }


}