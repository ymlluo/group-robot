<?php

namespace Ymlluo\GroupRobot;

use Ymlluo\GroupRobot\Contracts\Channel;
use Ymlluo\GroupRobot\Notify\Dingtalk;
use Ymlluo\GroupRobot\Notify\Feishu;
use Ymlluo\GroupRobot\Notify\Wechat;

class GroupRobot
{
    public $channel;
    public $configs =[];

    public function __construct($channel = '')
    {
        $this->channel = $this->resolve($channel);
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
            return null;
        }
        $method = ucfirst($name) . 'Notify';
        if (method_exists($this, $method)) {
           return call_user_func([$this,$method]);
        } else {
            throw new InvalidArgumentException("Channel [{$name}] is not supported.");
        }
    }


    /**
     * 飞书
     */
    public function FeishuNotify()
    {
       return new Feishu();
    }

    /**
     * 企业微信
     *
     * @return Wechat
     */
    public function WechatNotify()
    {
      return new Wechat();
    }

    /**
     * 钉钉
     */
    public function DingtalkNotify()
    {
        return new Dingtalk();
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
