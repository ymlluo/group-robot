<?php


namespace Ymlluo\GroupRobot\Notify;


use GuzzleHttp\Client;

class BaseNotify
{
    public $webhook;

    public $message;

    public $use_queue = false;

    public $message_queues = [];

    public $message_at = [];

    public $message_at_type;

    protected $secret;

    public $result;

    public $alias = '';

    public $name = '';



    public function alias(string $name)
    {
        $this->alias = $name;
        return $this;
    }

    public function name(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getName(): string
    {

        return $this->name;
    }


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
        throw new \Exception("channel handleFile function not set");
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
        }
        if (isset($this->message['file_queue'])) {
            $this->handleFile();
        }
        if (!isset($this->message)) {
            throw new \Exception('message not set!');
        }

        if (isset($this->message['at_allow']) && $this->message['at_allow']) {
            if ($this->message_at) {
                if (isset($this->message['at_append'])) {
                    $concat = $this->message['at_append'] === 'concat';
                    $this->arrayAppend($this->message, $this->message_at, $concat);
                }
                if (isset($this->message['at_key'])) {
                    $this->arrayAppend($this->message, [$this->message['at_key'] => $this->message_at]);
                }
            }
            unset($this->message['at_allow'], $this->message['at_key'], $this->message['at_concat'], $this->message['at_append']);
        }

        $response = $this->getClient()->post($this->webhook, ['json' => $this->message, 'http_errors' => false, 'verify' => false]);
        $result = json_decode((string)$response->getBody(), true);
        $this->result = $result;
        if ($this->message_queues) {
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
     * @param array $config
     * @return Client
     */
    public function getClient($config = [])
    {
        return new Client($config);
    }

    /**
     * 补充信息
     *
     * @param $array
     * @param $append
     * @param false $concat
     */
    protected function arrayAppend(&$array, $append, $concat = false)
    {
        if (is_array($append)) {
            foreach ($append as $k => $value) {
                if (isset($array[$k])) {
                    if (is_array($array[$k])) {
                        $this->arrayAppend($array[$k], $value, $concat);
                    } else {
                        if ($concat) {
                            $array[$k] .= $value;
                        }
                    }
                } else {
                    if ($k === 0) {
                        $array[] = $value;
                    } else {
                        $array[$k] = $value;
                    }
                }
            }
        }

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
