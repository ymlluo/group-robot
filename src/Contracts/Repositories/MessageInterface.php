<?php

namespace ymlluo\Contracts\Repositories;

interface MessageInterface
{

    /**
     * 纯文本消息
     *
     * @param string $content
     * @param  array $at
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
     * @param string $img
     * @return mixed
     */
    public function image(string $img);

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