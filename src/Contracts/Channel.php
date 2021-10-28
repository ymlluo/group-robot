<?php


namespace Ymlluo\GroupRobot\Contracts;


use phpDocumentor\Reflection\Types\Mixed_;

interface Channel
{

    /**
     * 设置 webhook 地址
     * @param mixed $webhook
     * @return mixed
     */
    public function to(string $webhook);

    /**
     * 发送消息
     *
     * @param null $webhook
     * @return mixed
     */
    public function send(string $webhook = '');


    /**
     * 纯文本消息
     *
     * @param string $content
     * @param array $at
     * @return mixed
     */
    public function text(string $content, array $at = []);

    /**
     * markdown 消息
     *
     * @param string $markdown
     * @param array $at
     * @return mixed
     */
    public function markdown(string $markdown, array $at = []);


    /**
     * 富文本消息
     *
     * @param array $content
     * @param array $at
     * @return mixed
     */
    public function rich(array $content, array $at = []);

    /**
     * 文件消息
     *
     * @param string $path
     * @param string $filename
     * @return mixed
     */
    public function file(string $path, string $filename = '');

    /**
     * 图片消息
     *
     * @param string $path
     * @return mixed
     */
    public function image(string $path);

    /**
     * 图文消息
     *
     * @param array $news
     * @return mixed
     */
    public function news(array $news);

    /**
     * 卡片消息
     *
     * @param array $card
     * @return mixed
     */
    public function card(array $card);

}