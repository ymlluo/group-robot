<?php

namespace Ymlluo\GroupRobot;

use ymlluo\Contracts\Channel;
use ymlluo\Notify\Feishu;

class GroupRobot
{
    protected $channel;

    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    public function channel($name)
    {
        $this->resolve($name);
    }

    protected function resolve($name)
    {
        $method = ucfirst($name).'BaseNotify';

        if (method_exists($this, $method)) {
            return $this->{$method};
        } else {
            throw new InvalidArgumentException("Channel [{$name}] is not supported.");
        }
    }


    public function FeishuNotify()
    {
        $this->channel = new Feishu();
    }



}