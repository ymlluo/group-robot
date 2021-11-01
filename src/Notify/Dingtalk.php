<?php


namespace Ymlluo\GroupRobot\Notify;


use Ymlluo\GroupRobot\Contracts\Channel;

class Dingtalk extends BaseNotify implements Channel
{

    public function text(string $content)
    {
        $this->message_at_type = 'text';
        $this->message = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ],
            'at_allow'=>true,
            'at_append'=>'merge'
        ];
        $this->addQueue();
        return $this;
    }

    public function markdown(string $markdown, string $title = '')
    {
        $this->message_at_type = 'markdown';
        $this->message = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => $title ?? "图文消息",
                'text' => $markdown
            ],
            'at_allow'=>true,
            'at_append'=>'merge'
        ];
        $this->addQueue();
        return $this;
    }

    public function file(string $path, string $filename = '')
    {
        return $this->markdown("[$filename]($path)", $filename);
    }

    public function image(string $path)
    {
        return $this->markdown("![image]($path)", "图片消息");
    }

    public function news(array $news)
    {
        if (!is_array(current($news))) {
            return $this->link($news);
        } else {
            return $this->feedCard($news);
        }
    }

    /**
     * link 消息
     *
     * @param $data
     * @return $this
     */
    public function link($data)
    {
        $this->message = [
            'msgtype' => 'link',
            'link' => [
                'text' => $data['text'] ?? $data['description'] ?? "",
                'title' => $data['title'],
                'picUrl' => $data['picUrl'] ?? $data['picurl'] ?? '',
                'messageUrl' => $data['messageUrl'] ?? $data['url'] ?? ''
            ]
        ];
        $this->addQueue();
        return $this;
    }

    /**
     * FeedCard 消息
     *
     * @param $lists
     * @return $this
     */
    public function feedCard($lists)
    {
        $this->message = [
            'msgtype' => 'feedCard',
            'feedCard' => [
                'links' => array_map(function ($news) {
                    return [
                        'title' => $news['title'],
                        'picURL' => $news['picURL'] ?? $news['picurl'] ?? '',
                        'messageURL' => $news['picURL'] ?? $news['url'] ?? ''
                    ];
                }, $lists)
            ]
        ];
        $this->addQueue();
        return $this;
    }


    public function card(string $title, string $description, string $image, string $url, array $buttons = [], array $extra = [])
    {
        $imageStr = $image ? "![screenshot]($image) \n\n" : "";
        $this->message = [
            'msgtype' => 'actionCard',
            'actionCard' => [
                'title' => $title,
                'text' => $imageStr . $description,
                'btnOrientation' => $extra['btnOrientation'] ?? "0",
                'actionURL' => $url
            ],
        ];
        if ($buttons && isset($buttons['title']) && isset($buttons['url'])) {
            $this->message['actionCard']['singleTitle'] = $buttons['title'];
            $this->message['actionCard']['singleURL'] = $buttons['url'];
        } else {
            foreach ($buttons as $button) {
                $this->message['actionCard']['btns'][] = [
                    'title' => $button['title'],
                    'actionURL' => $button['url']
                ];
            }

        }
        $this->addQueue();

        return $this;
    }

    /**
     * 独立跳转ActionCard
     *
     * @param $data
     * @return $this
     */
    public function actionCard($data)
    {
        $this->message = [
            'msgtype' => 'actionCard',
            'actionCard' => $data
        ];
        $this->addQueue();
        return $this;
    }


    /**
     * at users
     *
     * @param array $userIds
     * @param bool $isAll
     * @return mixed|void
     */
    public function atUsers(array $userIds, bool $isAll = false)
    {
        if (!isset($this->message_at['at']['atUserIds'])){
            $this->message_at['at']['atUserIds'] = [];
        }
        $this->message_at['at']['atUserIds'] = array_merge((array)$this->message_at['at']['atUserIds']??[],$userIds);
        $this->atAll($isAll);
        return $this;
    }

    /**
     * at mobiles
     *
     * @param array $phoneNums
     * @param bool $isAll
     * @return mixed|void
     */
    public function atMobiles(array $phoneNums, bool $isAll = false)
    {
        if (!isset($this->message_at['at']['atMobiles'])){
            $this->message_at['at']['atMobiles'] = [];
        }
        $this->message_at['at']['atMobiles'] = array_merge((array)$this->message_at['at']['atMobiles']??[],$phoneNums);
        $this->atAll($isAll);
        return $this;

    }

    public function atAll(bool $isAll = true)
    {
        $this->message_at['at']['isAtAll'] = $isAll;
        return $this;
    }


    public function makeSignature()
    {
        $t = intval(microtime(true) * 1000);
        $this->webhook = $this->webhook . '&timestamp=' . $t . '&sign=' . urlencode(base64_encode(hash_hmac('sha256', $t . "\n" . $this->secret, $this->secret, true)));
    }
}
