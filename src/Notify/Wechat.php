<?php


namespace ymlluo\Notify;


use phpDocumentor\Reflection\Types\This;
use ymlluo\Contracts\Channel;

class Wechat extends BaseNotify implements Channel
{

    public function text(string $content, array $at = [])
    {
        $this->message = [
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ]
        ];
        if ($at) {
            $this->message = array_merge($this->message['text'], $at);
        }
        return $this;
    }

    public function markdown(string $markdown, array $at = [])
    {
        $this->message = [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => $markdown
            ]
        ];
        if ($at) {
            $this->message = array_merge($this->message['markdown'], $at);
        }
        return $this;
    }

    public function rich(array $content, array $at = [])
    {
        throw new \Exception("channel don't support rice message");
    }

    public function file(string $path, string $filename = '')
    {
        $this->message = [
            'msgtype' => 'file',
            'file' => [
                'media_id' => $this->getWechatMediaIdByFile($path, $filename)
            ]
        ];
        return $this;
    }

    public function image(string $img)
    {
        if (filter_var($img, FILTER_VALIDATE_URL)) {
            $extension = pathinfo(parse_url($img, PHP_URL_PATH), PATHINFO_EXTENSION);
            $img = tempnam(sys_get_temp_dir(), uniqid() . '.' . $extension);
        }
        $this->message = [
            'msgtype' => 'image',
            'image' => [
                'base64' => base64_encode(file_get_contents($img)),
                'md5' => md5($img)
            ]
        ];
        return $this;
    }

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

    public function card(array $card)
    {
        $this->message = [
            'msgtype' => 'template_card',
            'template_card' => $card
        ];
        return $this;
    }


    protected function getWechatMediaIdByFile($path, $filename = '')
    {
        $fileHash = md5($path);
        if ($cache = cache()->get("WX:MEDIA:ID:$fileHash")) {
            return $cache;
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
        $res = $this->httpClient()->post($url, [
            'multipart' => [
                'name' => 'media',
                'content' => file_get_contents($path),
                'filename' => $filename
            ]
        ]);
        if ($res->getStatusCode() === 200) {
            $json = json_decode($res->getBody());
            $media_id = $json['media_id'];
            cache()->set("WX:MEDIA:ID:$fileHash", $media_id, 86400);
//            unlink($path);
            return $media_id;
        }
        throw new \Exception('upload file to wechat server error', 10012);
    }

}