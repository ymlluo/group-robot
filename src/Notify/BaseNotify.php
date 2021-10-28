<?php


namespace Ymlluo\GroupRobot\Notify;


use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use Ymlluo\GroupRobot\GroupRobot;

class BaseNotify
{
    public $webhook;

    public $message;


    public function __construct()
    {
    }

    /**
     * set webhook url
     *
     * @param $webhook
     * @return $this
     */
    public function to(string $webhook)
    {
        $this->webhook = $webhook;
        return $this;
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
     * 发送消息
     *
     * @param null $webhook
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $webhook = '')
    {
        if (!isset($this->message)) {
            throw new \Exception('message not set!');
        }
        if ($webhook) {
            $this->webhook = $webhook;
        }
        if (!$this->webhook) {
            throw new \Exception('webhook not set');
        }

        $response = $this->getClient()->post($this->webhook, ['json' => $this->message, 'http_errors' => false,'verify'=>false]);
        $result = json_decode((string)$response->getBody(), true);
        return ['params' => $this->message, 'result' => $result];
    }

    /**
     * @param array $config
     * @return Client
     */
    public function getClient($config = [])
    {
        return new Client($config);
    }
}
