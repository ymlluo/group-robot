<?php


namespace Ymlluo\GroupRobot\Notify;


use Ymlluo\GroupRobot\Contracts\Channel;

class Feishu extends BaseNotify implements Channel
{

    public function text(string $content)
    {
        $this->message = [
            'msg_type' => 'text',
            'content' => json_encode(['text' => $content], JSON_UNESCAPED_UNICODE)
        ];
        return $this;
    }

    public function rich(array $data)
    {
        $this->message = [
            'msg_type' => 'post',
            'content' => json_encode([
                'post' => [
                    'zh_cn' => $data
                ]
            ], JSON_UNESCAPED_UNICODE)
        ];
        return $this;
    }


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
            ]
        ];
        if ($title) {
            $this->message['card']['header'] = [
                'title' => [
                    'content' => $title,
                    'tag' => 'plain_text'
                ]
            ];
        }
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
        $array = json_decode($this->message['content'], true);
        switch ($this->message_type) {
            case 'text':
                $array['text'] = $array['text'] . "<at user_id=\"all\">所有人</at>";
                break;
            case 'markdown':
                foreach ($array['card']['elements'] as $i => $element) {
                    if ($element['tag'] === 'markdown') {
                        $array['card']['elements'][$i]['content'] .= '<at id=all></at>';
                    }
                }
                break;
            case 'rich':
                $array[] = ['tag' => 'at', 'user_id' => 'all', 'user_name' => '所有人'];
                break;
        }
        $this->message['content'] = json_encode($array, JSON_UNESCAPED_UNICODE);
    }


    public function makeSignature()
    {
        $t = time();
        $sign = base64_encode(hash_hmac('sha256', '', $t . "\n" . $this->secret, true));
        $this->message['timestamp'] = strval($t);
        $this->message['sign'] = $sign;
    }
}
