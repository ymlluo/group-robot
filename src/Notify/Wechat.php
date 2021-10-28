<?php


namespace Ymlluo\GroupRobot\Notify;

use Ymlluo\GroupRobot\Contracts\Channel;

class Wechat extends BaseNotify implements Channel
{
    public $filePending = null;

    /**
     * 文本消息
     *
     * @param string $content
     * @param array $at
     * @return $this|mixed
     */
    public function text(string $content, array $at = [])
    {
        $this->message = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ]
        ];
        if ($at) {
            $this->message['text'] = array_merge($this->message['text'], $at);
        }
        return $this;
    }

    /**
     *  markdown 消息
     *
     * @param string $markdown
     * @param array $at
     * @return $this|mixed
     */
    public function markdown(string $markdown, array $at = [])
    {
        $this->message = [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => $markdown
            ]
        ];
        if ($at) {
            $this->message['markdown'] = array_merge($this->message['markdown'], $at);
        }
        return $this;
    }

    /**
     * 富文本消息
     *
     * @param array $content
     * @param array $at
     * @return mixed|void
     * @throws \Exception
     */
    public function rich(array $content, array $at = [])
    {
        throw new \Exception("channel don't support rice message");
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
        $this->filePending = ['path' => $path, 'filename' => $filename];
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
                'articles' => isset($news['title']) ? [$news] : $news
            ]
        ];
        return $this;
    }

    /**
     * 卡片消息
     *
     * @param array $card
     * @return $this|mixed
     */
    public function card(array $card)
    {
        $this->message = [
            'msgtype' => 'template_card',
            'template_card' => $card
        ];
        return $this;
    }


    /**
     * 上传本地文件换取 media_id 并设置消息
     *
     * @param $webhook
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function handleFile($webhook)
    {
        $path = $this->filePending['path'];
        $filename = $this->filePending['filename'];

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = $this->downloadFile($path);
        }
        if (!file_exists($path)) {
            throw new \Exception("file not exists");
        }
        parse_str(data_get(parse_url($webhook), 'query', ''), $q);
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
     * 从 url 下载文件到本地
     *
     * @param $url
     * @return false|string
     */
    protected function downloadFile($url)
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $path = tempnam(sys_get_temp_dir(),'') . '.' . $extension;
        $this->getClient()->request('GET',$url,['sink'=>$path,'verify'=>false]);
        return $path;
    }

    /**
     * 发送消息
     *
     * @param string|null $webhook
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $webhook = '')
    {
        if ($webhook){
            $this->webhook = $webhook;
        }
        if ($this->filePending) {
            $this->handleFile($this->webhook);
        }
        return parent::send($this->webhook);
    }

}
