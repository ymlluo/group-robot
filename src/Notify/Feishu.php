<?php


namespace Ymlluo\GroupRobot\Notify;


use Ymlluo\GroupRobot\Contracts\Channel;

class Feishu extends BaseNotify implements Channel
{

    public function text(string $content)
    {
        $this->message_at_type = 'text';
        $this->message = [
            'msg_type' => 'text',
            'content' => [
                'text' => $content
            ],
            'at_allow' => true,
            'at_append' => 'concat'
        ];
        $this->addQueue();
        return $this;
    }

    public function rich(array $data)
    {
        $this->message_at_type = 'rich';
        $this->message = [
            'msg_type' => 'post',
            'content' => [
                'post' => [
                    'zh_cn' => $data
                ]
            ],
            'at_allow' => true,
            'at_append' => 'merge'
        ];
        $this->addQueue();
        return $this;
    }


    public function markdown(string $markdown, string $title = '')
    {
        $this->message_at_type = 'interactive';
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
     * 发送文件
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
     * 图片
     *
     * @param string $path
     * @return mixed|void
     * @throws \Exception
     */
    public function image(string $path)
    {
        throw new \Exception("Unsupported message type");
    }

    public function news(array $news)
    {
        throw new \Exception("Unsupported message type");
    }

    public function card(string $title, string $description, string $image, string $url, array $buttons = [], array $extra = [])
    {
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

    public function atUsers(array $userIds, bool $isAll = false)
    {
        // TODO: Implement atUsers() method.
    }

    public function atMobiles(array $phoneNums, bool $isAll = false)
    {
        // TODO: Implement atMobiles() method.
    }

    public function atAll(bool $isAll = true)
    {
        switch ($this->message_at_type) {
            case 'text':
                $this->message_at['content']['text'] = "<at user_id=\"all\">所有人</at>";
                break;
            case 'interactive':
                foreach ($this->message['card']['elements'] as $i => $element) {
                    if ($element['tag'] === 'markdown') {
                        $this->message_at['card']['elements'][$i]['content'] = '<at id=all></at>';
                    }
                }
                break;
            case 'rich':
                $this->message_at['content']['post']['zh_cn'][] = ['tag' => 'at', 'user_id' => 'all', 'user_name' => '所有人'];
                break;
        }
        return $this;
    }


    public function makeSignature()
    {
        $t = time();
        $sign = base64_encode(hash_hmac('sha256', '', $t . "\n" . $this->secret, true));
        $this->message['timestamp'] = strval($t);
        $this->message['sign'] = $sign;
    }
}
