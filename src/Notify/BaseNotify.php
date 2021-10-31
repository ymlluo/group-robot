<?php


namespace Ymlluo\GroupRobot\Notify;


use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use Ymlluo\GroupRobot\Contracts\Channel;
use Ymlluo\GroupRobot\GroupRobot;

class BaseNotify
{
    public $webhook;

    public $message;

    public $file_queues = null;

    public $message_type;

    protected $secret;

    public $result;


    public function __construct()
    {

    }

    public function secret(string $secret)
    {
        $this->secret = $secret;
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

    protected function handleFile()
    {
        throw new \Exception("channel handleFile function not set");
    }


    /**
     * download file to local
     *
     * @param $url
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function downloadFile($url)
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $path = tempnam(sys_get_temp_dir(), '') . '.' . $extension;
        $this->getClient()->request('GET', $url, ['sink' => $path, 'verify' => false]);
        return $path;
    }

    /**
     * 发送消息
     *
     * @param null $webhook
     * @return array
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
        if ($this->secret) {
            $this->makeSignature();
        }
        if ($this->file_queues) {
            $this->handleFile();
        }
//        dd($this->webhook);
        $response = $this->getClient()->post($this->webhook, ['json' => $this->message, 'http_errors' => false, 'verify' => false]);
        $result = json_decode((string)$response->getBody(), true);
        $this->result = $result;
        return $result;
    }

    /**
     * 发送结果
     *
     * @return mixed
     */
    public function result()
    {
        return $this->result;
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
