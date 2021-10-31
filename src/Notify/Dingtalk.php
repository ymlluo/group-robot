<?php


namespace Ymlluo\GroupRobot\Notify;


use Ymlluo\GroupRobot\Contracts\Channel;

class Dingtalk extends BaseNotify implements Channel
{

    public function text(string $content)
    {
        $this->message_type = 'text';

        $this->message = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ]
        ];
        return $this;
    }

    public function markdown(string $markdown, string $title = '')
    {
        $this->message_type = 'markdown';
        $this->message = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => $title,
                'text' => $markdown
            ]
        ];
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
        if (isset($news['title'])) {
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
        return $this;
    }


    /**
     * at users
     *
     * @param array $userIds
     * @param bool $isAll
     * @return mixed|void
     */
    public function atUsers(array $userIds, bool $isAll)
    {
        if ($this->message_type) {
            $this->message['at']['atUserIds'] = array_merge((array)$this->message['at']['atUserIds'] ?? [], $userIds);
            $this->atAll($isAll);
        }
    }

    /**
     * at mobiles
     *
     * @param array $phoneNums
     * @param bool $isAll
     * @return mixed|void
     */
    public function atMobiles(array $phoneNums, bool $isAll)
    {
        if ($this->message_type) {
            $this->message['at']['atMobiles'] = array_merge((array)$this->message['at']['atMobiles'] ?? [], $phoneNums);
            $this->atAll($isAll);
        }
    }

    public function atAll(bool $isAll)
    {
        if ($this->message_type) {
            $this->message['at']['isAtAll'] = $isAll;
        }
    }


    public function makeSignature()
    {
        $t = intval(microtime(true) * 1000);
        $this->webhook = $this->webhook . '&timestamp=' . $t . '&sign=' . urlencode(base64_encode(hash_hmac('sha256', $t . "\n" . $this->secret, $this->secret, true)));
    }
}
