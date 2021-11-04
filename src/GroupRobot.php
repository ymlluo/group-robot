<?php

namespace Ymlluo\GroupRobot;

use Ymlluo\GroupRobot\Contracts\Platform;
use Ymlluo\GroupRobot\Notify\Dingtalk;
use Ymlluo\GroupRobot\Notify\Feishu;
use Ymlluo\GroupRobot\Notify\Wechat;

/**
 * Class GroupRobot
 * @package Ymlluo\GroupRobot
 *
 * @method \Ymlluo\GroupRobot\GroupRobot raw(array $data)
 * @method \Ymlluo\GroupRobot\GroupRobot text(string $content)
 * @method \Ymlluo\GroupRobot\GroupRobot markdown(string $markdown, string $title = '')
 * @method \Ymlluo\GroupRobot\GroupRobot file(string $path, string $filename = '')
 * @method \Ymlluo\GroupRobot\GroupRobot image(string $path)
 * @method \Ymlluo\GroupRobot\GroupRobot news(array $news)
 * @method \Ymlluo\GroupRobot\GroupRobot card(string $title, string $description, string $image, string $url, array $buttons = [], array $extra = [])
 * @method \Ymlluo\GroupRobot\GroupRobot atUsers(array $userIds, bool $isAll = false)
 * @method \Ymlluo\GroupRobot\GroupRobot atMobiles(array $phoneNums, bool $isAll = false)
 * @method \Ymlluo\GroupRobot\GroupRobot atAll(bool $isAll = true);
 * @method \Ymlluo\GroupRobot\GroupRobot makeSignature();
 * @method \Ymlluo\GroupRobot\GroupRobot queue()
 * @method \Ymlluo\GroupRobot\GroupRobot getClient($config = [])
 *
 *
 *
 */
class GroupRobot
{
    public $platform;


    public $copies = [];

    public $buffers = [];

    public $result = null;

    public $results = [];

    /**
     * GroupRobot constructor.
     * @param mixed $platform 发送平台，wechat:企业微信,dingtalk:钉钉,feishu:飞书
     * @param string $webhook webhook 地址
     * @param string $secret 秘钥,加密方式加签时必填，企业微信不支持这个参数
     * @param string $alias 别名，多渠道发送时方便区分结果
     */
    public function __construct($platform = '', string $webhook = '', string $secret = '', string $alias = '')
    {
        if ($platform) {
            if ($platform instanceof Platform) {
                $this->platform = $platform;
            }
            if (is_string($platform)) {
                $this->platform = $this->resolve($platform);
            }
            $this->alias($alias);
            $this->to($webhook);
            $this->secret($secret);
        }
    }


    /**
     * 设置平台
     *
     * @param string $name
     * @return $this
     */
    public function platform(string $name): GroupRobot
    {
        $this->platform = $this->resolve($name);
        return $this;
    }

    /**
     *  platform 的别名
     *
     * @param string $name
     * @return $this
     */
    public function channel(string $name): GroupRobot
    {
        $this->platform = $this->resolve($name);
        return $this;
    }

    /**
     * 设置 webhook
     * @param string $webhook
     * @return $this
     */
    public function to(string $webhook)
    {
        if ($this->platform) {
            $this->platform->to($webhook);
        }
        return $this;
    }

    /**
     * 设置平台别名
     *
     * @param string $alias
     * @return $this
     */
    public function alias(string $alias)
    {
        if ($this->platform) {
            $this->platform->alias($alias);
        }
        return $this;
    }

    /**
     *
     * @param string $secret
     * @return $this
     */
    public function secret(string $secret)
    {
        if ($this->platform) {
            $this->platform->secret($secret);
        }
        return $this;
    }

    /**
     * 自定义扩展
     *
     * @param mixed $platform
     * @param string $webhook
     * @param string $secret
     * @param string $alias
     * @return $this
     */
    public function extendPlatform(Platform $platform, string $webhook, string $secret = '', string $alias = ''): GroupRobot
    {
        $this->copies[] = new self($platform, $webhook, $secret, $alias);
        return $this;
    }


    public function resolve($platform)
    {
        if (!$platform) {
            return null;
        }
        $method = ucfirst($platform) . 'Notify';
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method]);
        } else {
            throw new InvalidArgumentException("Platform [{$platform}] is not supported.");
        }
    }


    /**
     * 飞书
     */
    protected function FeishuNotify()
    {
        return new Feishu();
    }

    /**
     * 企业微信
     *
     */
    protected function WechatNotify()
    {
        return new Wechat();
    }

    /**
     * 钉钉
     *
     * @return Dingtalk
     */
    protected function DingtalkNotify(): Dingtalk
    {
        return new Dingtalk();
    }


    /**
     * Pass dynamic methods call onto platform.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $method, array $parameters)
    {
        $this->buffers[] = [$method, $parameters];
        return $this;
    }

    /**
     *
     * @param mixed $platform
     * @param string $webhook
     * @param string $secret
     * @param string $alias
     * @return $this
     */
    public function cc($platform, string $webhook, string $secret = '', string $alias = '')
    {
        $this->copies[] = new self($platform, $webhook, $secret, $alias);
        return $this;
    }


    /**
     *
     * @return array
     */
    public function send(): array
    {
        if (!$this->platform && $this->copies) {
            $this->platform = array_shift($this->copies)->platform;
        }
        foreach ($this->buffers as $key => $buffer) {
            if (method_exists(get_class($this->platform), $buffer[0])) {
                call_user_func_array([$this->platform, $buffer[0]], $buffer[1]);
            }
        }
        $this->result = $this->platform->send();
        $this->results[] = $this->result;
        if ($this->copies) {
            $this->platform = array_shift($this->copies)->platform;
            $this->send();
        }
        $this->buffers = [];
        return $this->getResults();
    }

    public function message()
    {
        return $this->platform->message;
    }

    public function result($withMessage = false)
    {
        if ($withMessage) {
            return [
                'message' => $this->message(),
                'result' => $this->result()
            ];
        }
        return $this->result;
    }

    /**
     *
     * @return array
     */
    public function getResults(): array
    {
        return array_merge([], ...$this->results);
    }

}
