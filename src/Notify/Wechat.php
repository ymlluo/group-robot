<?php


namespace Ymlluo\GroupRobot\Notify;

use Ymlluo\GroupRobot\Contracts\Channel;

class Wechat extends BaseNotify implements Channel
{
    /**
     * 文本消息
     *
     * @param string $content
     * @param array $at
     * @return $this|mixed
     */
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

    /**
     *  markdown 消息
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
                'content' => $markdown
            ]
        ];
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
        $this->file_queues = ['path' => $path, 'filename' => $filename];
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

    public function card(string $title, string $description, string $image,string $url,array $buttons=[],array $extra=[])
    {
        $this->message =[
          'msgtype'=>'template_card',
            'template_card'=>[
                'card_type'=>'news_notice',
                'main_title'=>[
                    'title'=>$title,
                    'desc'=>$description
                ],
                'card_image'=>[
                    'url'=>$image,
                ],
                'vertical_content_list'=>[],
                'jump_list'=>$buttons,
                'card_action'=>[
                    'type'=>1,
                    'url'=>$url,

                ]
            ]
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
    protected function handleFile()
    {
        $path = $this->file_queues['path'];
        $filename = $this->file_queues['filename'];

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
    public function atUsers(array $userIds, bool $isAll=false)
    {
        if ($this->message_type) {
            $this->message[$this->message_type]['mentioned_list'] = array_merge((array)$this->message[$this->message_type]['mentioned_list'] ?? [], $userIds);
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
    public function atMobiles(array $phoneNums, bool $isAll=false)
    {
        if ($this->message_type) {
            $this->message[$this->message_type]['mentioned_mobile_list'] = array_merge((array)$this->message[$this->message_type]['mentioned_mobile_list'] ?? [], $phoneNums);
            $this->atAll($isAll);
        }
    }

    public function atAll(bool $isAll=true)
    {
        if ($this->message_type) {
            $this->message[$this->message_type]['mentioned_list'] = array_merge((array)$this->message[$this->message_type]['mentioned_list'] ?? [], ["@all"]);
        }
    }


    public function makeSignature()
    {
        // TODO: Implement makeSignature() method.
    }
}
