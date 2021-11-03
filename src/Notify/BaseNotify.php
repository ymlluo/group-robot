<?php


namespace Ymlluo\GroupRobot\Notify;


use GuzzleHttp\Client;

class BaseNotify
{
    /** @var string webhook url */
    public $webhook = '';

    /** @var array 消息详情 */
    public $message = [];

    /** @var bool 使用队列发送 */
    public $use_queue = false;

    /** @var array 消息队列缓存 */
    public $message_queues = [];

    /** @var array @某人 */
    public $message_at = [];


    /** @var string 平台名称 */
    protected $platform = '';

    /** @var string 秘钥 */
    protected $secret = '';

    /** @var array 发送结果 */
    public $result = [];

    /** @var string 机器人别名 */
    public $alias = '';

    /** @var string 机器人名称 */
    public $name = '';

    /**
     * 设置机器人别名
     *
     * @param string $name
     * @return $this
     */
    public function alias(string $name)
    {
        $this->alias = $name;
        return $this;
    }

    /**
     * 设置机器人名称
     *
     * @param string $name
     * @return $this
     */
    public function name(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 机器人别名
     *
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * 机器人名称
     *
     * @return string
     */
    public function getName(): string
    {

        return $this->name;
    }

    /**
     * 设置秘钥
     *
     * @param string $secret
     * @return $this
     */
    public function secret(string $secret)
    {
        $this->secret = $secret;
        return $this;
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
        $this->addQueue();
        return $this;
    }

    protected function handleFile()
    {
        throw new \Exception("platform handleFile function not set");
    }

    public function queue()
    {
        $this->use_queue = true;
        return $this;
    }

    protected function addQueue()
    {
        $this->message_queues[] = $this->message;
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

        if (!$this->webhook) {
            throw new \Exception('webhook not set');
        }
        if ($this->secret) {
            $this->makeSignature();
        }
        if ($this->use_queue && $this->message_queues) {
            $this->message = array_shift($this->message_queues);
        } else {
            $this->message_queues = [];
        }
        if (isset($this->message['file_queue'])) {
            $this->handleFile();
        }
        if (!isset($this->message)) {
            throw new \Exception('message not set!');
        }
        if ($this->message_at) {
            $this->concatAt();
        }
        $response = $this->getClient()->post($this->webhook, ['json' => $this->message, 'http_errors' => false, 'verify' => false, 'timeout' => 10]);
        $result = json_decode((string)$response->getBody(), true);
        $this->result = $result;
        if ($this->use_queue && $this->message_queues) {
            return $this->send();
        }

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
     * HTTP 客户端
     *
     * @param array $config
     * @return Client
     */
    public function getClient($config = [])
    {
        return new Client($config);
    }

    /**
     * 分割字符串
     *
     * @param string $string
     * @param int $maxLength
     * @param string $separator
     * @return array
     */
    protected function chunkStrings(string $string, int $maxLength, string $separator = "\n"): array
    {
        do {
            $msg = substr($string, 0, $maxLength);
            $split = substr($msg, 0, intval(strripos($msg, $separator)) ?: $maxLength);
            $string = substr($string, strlen($split));
            $queues[] = $split;
        } while (strlen($string) >= $maxLength);
        if (strlen($string)) {
            $queues[] = $string;
        }
        return $queues;
    }


}
