<?php


namespace Ymlluo\GroupRobot\Notify;

use Ymlluo\GroupRobot\Contracts\Platform;

class Wechat extends BaseNotify implements Platform
{
    protected $platform = 'wechat';

    public $max_text_length = 2048;
    public $max_md_length = 4096;

    /**
     * 文本消息
     *
     * @param string $content
     * @param array $at
     * @return $this|mixed
     */
    public function text(string $content)
    {
        if (strlen($content) > $this->max_text_length) {
            $this->queue();
            $lists = $this->chunkStrings($content, $this->max_text_length);
            foreach ($lists as $text) {
                $this->text($text);
            }
            return $this;
        }
        $this->message = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ],
        ];
        $this->addQueue();
        return $this;
    }

    /**
     *  markdown 消息
     *
     * @param string $markdown
     * @param string $title
     * @return $this|mixed
     */
    public function markdown(string $markdown, string $title = '')
    {
        if (strlen($markdown) > $this->max_md_length) {
            $this->queue();
            $lists = $this->chunkStrings($markdown, $this->max_md_length);
            foreach ($lists as $text) {
                $this->markdown($text, $title);
            }
            return $this;
        }

        $this->message = [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => $markdown
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
        $this->message['file_queue'] = ['path' => $path, 'filename' => $filename];
        $this->addQueue();
        return $this;
    }


    /**
     *  图片消息
     *
     * @param string $path
     * @return $this|mixed
     */
    public function image(string $path)
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = $this->downloadFile($path);
        }
        $this->message = [
            'msgtype' => 'image',
            'image' => [
                'base64' => base64_encode(file_get_contents($path)),
                'md5' => md5_file($path)
            ]
        ];
        $this->addQueue();
        return $this;
    }

    /**
     * 图文消息
     *
     * @param array $news
     * @return $this|mixed
     */
    public function news(array $news)
    {
        $this->message = [
            'msgtype' => 'news',
            'news' => [
                'articles' => !is_array(current($news)) ? [$news] : $news
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
        $this->message = [
            'msgtype' => 'template_card',
            'template_card' => [
                'card_type' => 'news_notice',
                'main_title' => [
                    'title' => $title,
                    'desc' => $description
                ],
                'card_image' => [
                    'url' => $image,
                ],
                'vertical_content_list' => [],
                'jump_list' => array_map(function ($btn) {
                    return [
                        'type' => $btn['type'] ?? 1,
                        'title' => $btn['title'] ?? '',
                        'url' => $btn['url'] ?? '',
                    ];
                }, is_array(current($buttons)) ? $buttons : [$buttons]),
                'card_action' => [
                    'type' => 1,
                    'url' => $url,

                ]
            ]
        ];
        $this->addQueue();
        return $this;
    }

    /**
     * 模板消息
     *
     * @param array $data
     * @return $this
     */
    public function template_card(array $data)
    {
        $this->message = [
            'msgtype' => 'template_card',
            'template_card' => $data
        ];
        $this->addQueue();
        return $this;
    }


    /**
     * 上传本地文件换取 media_id 并设置消息
     *
     * @param $webhook
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function handleFile()
    {
        if (isset($this->message['file_queue'])) {
            $path = $this->message['file_queue']['path'];
            $filename = $this->message['file_queue']['filename'];
            unset($this->message['file_queue']);
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = $this->downloadFile($path);
        }
        if (!file_exists($path)) {
            throw new \Exception("file not exists");
        }
        parse_str(data_get(parse_url($this->webhook), 'query', ''), $q);
        if (!$key = data_get($q, 'key')) {
            throw new \Exception("get key from url error");
        }
        if (!$filename) {
            $filename = basename($path);
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/webhook/upload_media?key=$key&type=file";
        $res = $this->getClient()->post($url, [
            'multipart' => [
                [
                    'name' => 'media',
                    'contents' => file_get_contents($path),
                    'filename' => $filename
                ]
            ]
        ]);
        if ($res->getStatusCode() === 200) {
            $json = json_decode($res->getBody(), true);
            if ($media_id = $json['media_id'] ?? null) {
                $this->message = [
                    'msgtype' => 'file',
                    'file' => [
                        'media_id' => $media_id
                    ]
                ];
                return $this;
            } else {
                throw new \Exception('get media id error');
            }
        }
        throw new \Exception('upload file to wechat server error', 10012);
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
        if (!isset($this->message_at['mentioned_list'])) {
            $this->message_at['mentioned_list'] = [];
        }
        $this->message_at['mentioned_list'] = array_values(array_unique(array_merge((array)$this->message_at['mentioned_list'] ?? [], $userIds)));
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
        if (!isset($this->message_at['mentioned_mobile_list'])) {
            $this->message_at['mentioned_mobile_list'] = [];
        }
        $this->message_at['mentioned_mobile_list'] = array_values(array_unique(array_merge((array)$this->message_at['mentioned_mobile_list'] ?? [], $phoneNums)));;
        $this->atAll($isAll);
        return $this;

    }

    /**
     * @all
     * @param bool $isAll
     * @return $this|mixed
     */
    public function atAll(bool $isAll = true)
    {
        if (!isset($this->message_at['mentioned_list'])) {
            $this->message_at['mentioned_list'] = [];
        }
        if ($isAll) {
            $this->message_at['mentioned_list'] = array_values(array_unique(array_merge((array)$this->message_at['mentioned_list'] ?? [], ["@all"])));
        } else {
            $this->message_at['mentioned_list'] = array_values(array_unique(array_filter($this->message_at['mentioned_list'], function ($item) {
                return $item !== '@all';
            })));
        }
        return $this;
    }


    /**
     * 合并 @xxx
     * @return mixed|void
     */
    public function concatAt()
    {

        if ($this->message['msgtype'] === 'text') {
            $this->message['text'] = array_merge($this->message['text'], $this->message_at);
        } elseif ($this->message['msgtype'] === 'markdown') {
            $this->message['markdown'] = array_merge($this->message['markdown'], $this->message_at);
            foreach ($this->message_at as $k => $info) {
                if ($k === 'mentioned_list') {
                    $this->message['markdown']['content'] .= implode(' ', array_map(function ($item) {
                        return ' <@' . ltrim($item, '@') . '>';
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
        // TODO: Implement makeSignature() method.
    }
}
