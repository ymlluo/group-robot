<?php


namespace Ymlluo\GroupRobot\Notify;

use Ymlluo\GroupRobot\Contracts\Platform;

class Dingtalk extends BaseNotify implements Platform
{
    protected $platform = 'dingtalk';

    /**
     * 文本消息
     *
     * @param string $content
     * @return $this|mixed
     */
    public function text(string $content)
    {
        $this->message = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ]
        ];
        $this->addQueue();
        return $this;
    }

    /**
     * markdown 消息
     *
     * @param string $markdown
     * @param string $title
     * @return $this|mixed
     */
    public function markdown(string $markdown, string $title = '')
    {
        $this->message = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => $title ?? "图文消息",
                'text' => $markdown
            ]
        ];
        $this->addQueue();
        return $this;
    }

    /**
     * 文件消息
     *
     * @param string $path
     * @param string $filename
     * @return $this|mixed
     */
    public function file(string $path, string $filename = '')
    {
        return $this->markdown("[$filename]($path)", $filename);
    }

    /**
     * 图片消息
     *
     * @param string $path
     * @return $this|mixed
     */
    public function image(string $path)
    {
        return $this->markdown("![image]($path)", "图片消息");
    }

    /**
     * 图文消息
     *
     * @param array $news
     * @return $this|mixed
     */
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

    /**
     * 卡片消息
     *
     * @param string $title
     * @param string $description
     * @param string $image
     * @param string $url
     * @param array $buttons
     * @param array $extra
     * @return $this|mixed
     */
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
        if (!isset($this->message_at['atUserIds'])) {
            $this->message_at['atUserIds'] = [];
        }
        $this->message_at['atUserIds'] = array_values(array_unique(array_merge((array)$this->message_at['atUserIds'] ?? [], $userIds)));
        if ($isAll) {
            $this->atAll($isAll);
        }
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
        if (!isset($this->message_at['atMobiles'])) {
            $this->message_at['atMobiles'] = [];
        }
        $this->message_at['atMobiles'] = array_values(array_unique(array_merge((array)$this->message_at['atMobiles'] ?? [], $phoneNums)));
        if ($isAll) {
            $this->atAll($isAll);
        }
        return $this;

    }

    /**
     * @all
     *
     * @param bool $isAll
     * @return $this|mixed
     */
    public function atAll(bool $isAll = true)
    {
        $this->message_at['isAtAll'] = $isAll;
        return $this;
    }

    /**
     * 合并 @xxx
     *
     * @return mixed|void
     */
    public function concatAt()
    {
        $this->message['at'] = $this->message_at;
        if (isset($this->message_at['isAtAll']) && $this->message_at['isAtAll']) {
            if ($this->message['msgtype'] === 'markdown') {
                $this->message['markdown']['text'] .= " @all";
            }
            if ($this->message['msgtype'] === 'text') {
                $this->message['text']['content'] .= " @all";
            }
        } else {
            foreach ($this->message_at as $info) {
                if ($this->message['msgtype'] === 'markdown') {
                    $this->message['markdown']['text'] .= implode(' ', array_map(function ($item) {
                        return '@' . ltrim($item, '@');
                    }, $info));
                }
                if ($this->message['msgtype'] === 'text') {
                    $this->message['text']['content'] .= implode(' ', array_map(function ($item) {
                        return '@' . ltrim($item, '@');
                    }, $info));
                }
            }
        }
    }

    /**
     * 消息签名
     *
     * @return mixed|void
     */
    public function makeSignature()
    {
        $t = intval(microtime(true) * 1000);
        $this->webhook = $this->webhook . '&timestamp=' . $t . '&sign=' . urlencode(base64_encode(hash_hmac('sha256', $t . "\n" . $this->secret, $this->secret, true)));
    }
}
