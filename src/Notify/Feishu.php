<?php


namespace Ymlluo\GroupRobot\Notify;


use Ymlluo\GroupRobot\Contracts\Channel;

class Feishu extends BaseNotify implements Channel
{

    public function text(string $content)
    {
        $this->message = [
            'msg_type' => 'text',
            'content' => json_encode(['text' => $content],JSON_UNESCAPED_UNICODE)
        ];
        return $this;
    }

    public function markdown(string $markdown, string $title = '')
    {
        // TODO: Implement markdown() method.
    }

    public function file(string $path, string $filename = '')
    {
        // TODO: Implement file() method.
    }

    public function image(string $path)
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

    public function atUsers(array $userIds, bool $isAll)
    {
        // TODO: Implement atUsers() method.
    }

    public function atMobiles(array $phoneNums, bool $isAll)
    {
        // TODO: Implement atMobiles() method.
    }

    public function atAll(bool $isAll)
    {
        $array =  json_decode($this->message['content'],true);
        switch ($this->message_type){
            case 'text':
                $array['text'] = $array['text']."<at user_id=\"all\">所有人</at>";
                break;
            case 'markdown':

                break;
            case 'rich':
                $array[]=['tag'=>'at','user_id'=>'all','user_name'=>'所有人'];
                break;
        }
        $this->message['content'] = json_encode($array,JSON_UNESCAPED_UNICODE);
    }
}
