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
 * @method  \Ymlluo\GroupRobot\GroupRobot secret(string $secret)
 * @method \Ymlluo\GroupRobot\GroupRobot to(string $webhook)
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


    public function __construct($platform = '', $webhook = '', $secret = '', $alias = '')
    {
        if ($platform) {
            $this->platform = $this->resolve($platform);
            $this->platform->name($platform);
            $this->platform->alias($alias);
            $this->platform->to($webhook);
            $this->platform->secret($secret);
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
     * 自定义扩展
     *
     * @param Platform $platform
     * @param string $webhook
     * @param string $secret
     * @param string $name
     * @param string $alias
     * @return $this
     */
    public function extendPlatform(Platform $platform, $webhook = '', $secret = '', $name = '', $alias = ''): GroupRobot
    {
        $this->platform = $platform;
        $this->platform->name($name);
        $this->platform->alias($alias);
        $this->platform->to($webhook);
        $this->platform->secret($secret);
        $this->copies[] = $this;
        return $this;
    }


    public function resolve($name)
    {
        if (!$name) {
            return null;
        }
        $method = ucfirst($name) . 'Notify';
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method]);
        } else {
            throw new InvalidArgumentException("Platform [{$name}] is not supported.");
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
     * @param string $webhook
     * @param string $secret
     * @param string $name
     * @param string $alias
     * @return $this
     */
    public function cc(string $webhook, string $secret = '', string $name = '', string $alias = '')
    {
        if ($name || $this->platform) {
            $copy = new self($name ?: $this->platform->getName(), $webhook, $secret, $alias);
            $this->copies[] = $copy;
        }

        return $this;
    }


    /**
     *
     * @return array
     */
    public function send(): array
    {
        foreach ($this->buffers as $key => $buffer) {
            if (method_exists(get_class($this->platform), $buffer[0])) {
                call_user_func_array([$this->platform, $buffer[0]], $buffer[1]);
                if (in_array($buffer[0], ['secret', 'to', 'name', 'alias', 'result', 'atAll'])) {
                    unset($this->buffers[$key]);
                }
            }
        }
        $this->result = $this->platform->send();
        $this->results[] = [
            'name' => $this->platform->getName(),
            'alias' => $this->platform->getAlias(),
            'message' => $this->message(),
            'result' => $this->result()
        ];
        if ($this->copies) {
            $this->platform = array_shift($this->copies)->platform;
            $this->send();
        }
        $this->buffers = [];
        return $this->result;
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
        return $this->results;
    }

}
