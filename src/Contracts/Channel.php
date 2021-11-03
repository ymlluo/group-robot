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
    public function send();


    /**
     * 纯文本消息
     *
     * @param string $content
     * @return mixed
     */
    public function text(string $content);

    /**
     * markdown 消息
     *
     * @param string $markdown
     * @param string $title
     * @return mixed
     */
    public function markdown(string $markdown, string $title = '');


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
     * @param string $title
     * @param string $description
     * @param string $image
     * @param string $url
     * @param array $buttons ['title'=>'xxx','url'=>'https://xxx.com']
     * @return mixed
     */
    public function card(string $title, string $description, string $image, string $url, array $buttons = [], array $extra = []);


    /**
     * @用户
     *
     * @param array $userIds
     * @param bool $isAll
     * @return mixed
     */
    public function atUsers(array $userIds, bool $isAll = false);

    /**
     * @手机号
     * @param array $phoneNums
     * @param bool $isAll
     * @return mixed
     */
    public function atMobiles(array $phoneNums, bool $isAll = false);

    /**
     * @全部用户
     *
     * @param bool $isAll
     * @return mixed
     */
    public function atAll(bool $isAll = true);

    /**
     * 设置秘钥
     *
     * @param string $secret
     * @return mixed
     */
    public function secret(string $secret);

    /**
     * 秘钥签名
     * @return mixed
     */
    public function makeSignature();

    /**
     * 发送结果
     *
     * @return mixed
     */
    public function result();

    /**
     * 发送渠道名称
     * @param string $channelName
     * @return mixed
     */
    public function name(string $channelName);

    /**
     * 发送渠道别名
     *
     * @param string $alias
     * @return mixed
     */
    public function alias(string $alias);


}
