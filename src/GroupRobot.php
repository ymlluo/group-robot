<?php

namespace Ymlluo\GroupRobot;

use Ymlluo\Contracts\Channel;
use Ymlluo\Notify\Dingtalk;
use Ymlluo\Notify\Feishu;
use Ymlluo\Notify\Wechat;

class GroupRobot
{
    protected $channel;
    public $configs =[];

    public function __construct($channel = '',$configs = [])
    {
        $this->channel = $this->resolve($channel);
        if ($configs){
            $this->configs = $configs;
        }else{
            $this->configs = [];
        }
        $this->configs = $configs;
    }

    /**
     * 自定义 channel
     * @param Channel $channel
     */
    public function channel(Channel $channel)
    {
        $this->channel = $channel;
    }


    protected function resolve($name)
    {
        if (!$name) {
            return $this;
        }
        $method = ucfirst($name) . 'Notify';

        if (method_exists($this, $method)) {
            return $this->{$method};
        } else {
            throw new InvalidArgumentException("Channel [{$name}] is not supported.");
        }
    }


    /**
     * 飞书
     *
     * @return $this
     */
    public function FeishuNotify()
    {
        $this->channel = new Feishu();
        return $this;
    }

    /**
     * 企业微信
     *
     * @return $this
     */
    public function WechatNotify()
    {
        $this->channel = new Wechat();
        return $this;

    }

    /**
     * 钉钉
     *
     * @return $this
     */
    public function DingtalkNotify()
    {
        $this->channel = new Dingtalk();
        return $this;

    }


    /**
     * Pass dynamic methods call onto channel.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, array $parameters)
    {
        return call_user_func_array([$this->channel, $method], $parameters);
    }


}