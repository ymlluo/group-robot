<?php


namespace Ymlluo\GroupRobot\Notify;


use Ymlluo\GroupRobot\Contracts\Channel;

class Dingtalk extends BaseNotify implements Channel
{

    public function text(string $content, array $at = [])
    {
        // TODO: Implement text() method.
    }

    public function markdown(string $markdown, array $at = [])
    {
        // TODO: Implement markdown() method.
    }

    public function rich(array $content, array $at = [])
    {
        // TODO: Implement rich() method.
    }

    public function file(string $path, string $filename = '')
    {
        // TODO: Implement file() method.
    }

    public function image(string $img)
    {
        // TODO: Implement image() method.
    }

    public function news(array $news)
    {
        // TODO: Implement news() method.
    }

    public function card(array $card)
    {
        // TODO: Implement card() method.
    }
}
