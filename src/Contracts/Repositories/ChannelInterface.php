<?php


namespace ymlluo\Contracts\Repositories;


interface ChannelInterface
{

    /**
     * 设置 webhook 地址
     * @param string $webhook
     * @return mixed
     */
    public function setWebhook(string $webhook);

    /**
     * 发送消息
     *
     * @return mixed
     */
    public function send();
}