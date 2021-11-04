<?php


namespace Ymlluo\GroupRobot\Notify;

use Ymlluo\GroupRobot\Contracts\Platform;

class Feishu extends BaseNotify implements Platform
{
    protected $platform = 'feishu';

    /**
     * 文本消息
     *
     * @param string $content
     * @return $this|mixed
     */
    public function text(string $content)
    {
        $this->message = [
            'msg_type' => 'text',
            'content' => [
                'text' => $content
            ]
        ];
        $this->addQueue();
        return $this;
    }

    /**
     * 富文本消息
     *
     * @param array $data
     * @return $this
     */
    public function rich(array $data)
    {
        $this->message = [
            'msg_type' => 'post',
            'content' => [
                'post' => [
                    'zh_cn' => $data
                ]
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
            'msg_type' => 'interactive',
            'card' => [
                'config' => [
                    'wide_screen_mode' => true,
                    'enable_forward' => true
                ],
                'elements' => [
                    [
                        'tag' => 'markdown',
                        'content' => $markdown
                    ]
                ]
            ],
            'at_allow' => true,
            'at_append' => 'concat'
        ];
        if ($title) {
            $this->message['card']['header'] = [
                'title' => [
                    'content' => $title,
                    'tag' => 'plain_text'
                ]
            ];
        }
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
        if (!filter_var($path,FILTER_VALIDATE_URL)){
            return $this;
        }
        return $this->markdown("[$filename]($path)", $filename);
    }

    /**
     * 图片消息
     *
     * @param string $path
     * @return mixed|void
     * @throws \Exception
     */
    public function image(string $path)
    {
        if (!filter_var($path,FILTER_VALIDATE_URL)){
            return $this;
        }
        //todo 只能显示链接
        return $this->markdown("[image]($path)");
    }

    /**
     * 图文消息
     *
     * @param array $news
     * @return mixed|void
     * @throws \Exception
     */
    public function news(array $news)
    {
        //todo 需要解决 图片转 media id 的问题
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
        if ($buttons && !is_array(current($buttons))) {
            $buttons = [$buttons];
        }
        $this->message = [
            'msg_type' => 'interactive',
            'card' => [
                'config' => [
                    'wide_screen_mode' => true,
                    'enable_forward' => true
                ],
                'header' => [
                    'title' => [
                        'content' => $title,
                        'tag' => 'plain_text'
                    ],
                    'template' => $extra['template'] ?? ""
                ],
                'card_link' => [
                    'url' => $url
                ],
                'elements' => [
                    [
                        'tag' => 'hr'
                    ],
                    [
                        'tag' => 'markdown',
                        'content' => $description
                    ],
                    [
                        'tag' => 'action',
                        'actions' => array_map(function ($btn) {
                            return [
                                'tag' => 'button',
                                'text' => [
                                    'tag' => 'plain_text',
                                    'content' => $btn['title'] ?? "查看详情"
                                ],
                                'url' => $btn['url'] ?? "",
                                'type' => $btn['type'] ?? 'primary'
                            ];
                        }, $buttons)
                    ]
                ]
            ]

        ];
        $this->addQueue();
        return $this;
    }

    /**
     * 消息卡片
     *
     * @param array $data
     * @return $this
     */
    public function interactive(array $data)
    {
        $this->message = [
            'msg_type' => 'interactive',
            'card' => $data

        ];
        $this->addQueue();
        return $this;
    }

    /**
     * @用户名
     *
     * @param array $userIds
     * @param bool $isAll
     * @return mixed|void
     */
    public function atUsers(array $userIds, bool $isAll = false)
    {
        // TODO: Implement atUsers() method.
    }

    /**
     * @手机号
     *
     * @param array $phoneNums
     * @param bool $isAll
     * @return mixed|void
     */
    public function atMobiles(array $phoneNums, bool $isAll = false)
    {
        // TODO: Implement atMobiles() method.
    }

    /** @all */
    public function atAll(bool $isAll = true)
    {
        switch ($this->message['msg_type']) {
            case 'text':
                $this->message_at['text'] = "<at user_id=\"all\">所有人</at>";
                break;
            case 'interactive':
                $this->message_at['interactive'] = '<at id=all></at>';
                break;
            case 'post':
                $this->message_at['post'] = ['tag' => 'at', 'user_id' => 'all', 'user_name' => '所有人'];
                break;
        }
        return $this;
    }

    /**
     * 合并 @xxx
     */
    public function concatAt()
    {
        switch ($this->message['msg_type']) {
            case 'text':
                $this->message['content']['text'] .= $this->message_at['text'];
                break;
            case 'interactive':
                foreach ($this->message['card']['elements'] as $i => $element) {
                    if ($element['tag'] === 'markdown') {
                        $this->message['card']['elements'][$i]['content'] .= $this->message_at['interactive'];
                        break;
                    }
                }
                break;
            case 'post':
                $this->message['content']['post']['zh_cn']['content'][0][] = $this->message_at['post'];
                break;
        }
    }


    /**
     * 消息签名
     * @return mixed|void
     */
    public function makeSignature()
    {
        $t = time();
        $sign = base64_encode(hash_hmac('sha256', '', $t . "\n" . $this->secret, true));
        $this->message['timestamp'] = strval($t);
        $this->message['sign'] = $sign;
    }
}
