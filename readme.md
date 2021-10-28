# 群组机器人

[comment]: <> ([![Latest Version on Packagist][ico-version]][link-packagist])

[comment]: <> ([![Total Downloads][ico-downloads]][link-downloads])

[comment]: <> ([![Build Status][ico-travis]][link-travis])

[comment]: <> ([![StyleCI][ico-styleci]][link-styleci])

**目前支持的自定义群机器人和通用消息类型**

| APP   | 名称 | 文本 | markdown | 图文 | 卡片 |
| ------ | ---- | ------ | ------ | ------ | ------ |
| 企业微信 | wechat  | √ | √ |√ |  √ |
| 钉钉 | dingtalk  | √ | √ |√ |  √ |
| 飞书 | feishu  | √ | √ |√ |  √ |


## Installation

Via Composer

``` bash
$ composer require ymlluo/group-robot
```

## Usage

----




**初始化**
```php
$robot = new GroupRobot('wechat');

//设置 webhook 发送地址
$webhook = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=693a91f6-7xxx-4bc4-97a0-0ec2sifa5aaa';

$robot->to($webhook);

//或者初始化直接指定
$robot = new GroupRobot('wechat',$webhook);

//或者最后发送的时候设置
$robot = $robot->text('hello')->send($webhook);
```

---
### 企业微信
>[企业微信-群机器人配置说明](https://work.weixin.qq.com/api/doc/90000/90136/91770)


**文本**
``` php 
$robot->text('hello world', ['mentioned_list' => ['@all'],'mentioned_mobile_list'=>['@all']])->send();
```
**Markdown**
```php
$robot->markdown("实时新增用户反馈<font color=\"warning\">132例</font>，请相关同事注意。")->send();
```
**图片**
```php
//网络图片
$robot->image('http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png')->send();

//发送本地图片
$robot->image('/tmp/images/test_pic_msg1.png')->send();
```
**文件**
```php 
//网络文件
$robot->file('http://www.gov.cn/zhengce/pdfFile/2021_PDF.pdf','政府信息公开目录.pdf')->send();

//本地文件
$robot->file('/tmp/pdfFile/2021_PDF.pdf','政府信息公开目录.pdf')->send();
```
**图文**
```php 
//单条图文
$robot->news([
  'title' => '中秋节礼品领取',
  'description' => '今年中秋节公司有豪礼相送',
  'url' => 'www.qq.com',
  'picurl' => 'http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png'
  ])->send();
  
//多条图文  
$robot->news([
  [
    'title' => '胡彦斌，不再为爱情燃烧',
    'description' => '胡彦斌最被大众好奇的问题是：他现在会如何看待感情？',
    'url' => 'https://new.qq.com/omn/20211028/20211028A07EDT00.html',
    'picurl' => 'https://inews.gtimg.com/newsapp_bt/0/14116849898/1000'
  ], 
  [
    'title' => '中秋节礼品领取',
    'description' => '今年中秋节公司有豪礼相送',
    'url' => 'www.qq.com',
    'picurl' => 'http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png'
  ]
])->send();
```
**模版卡片**
```php 
$robot->card([
            'card_type' => 'text_notice',
            'source' => [
                'icon_url' => 'https://wework.qpic.cn/wwpic/252813_jOfDHtcISzuodLa_1629280209/0',
                'desc' => '企业微信',
            ],
            'main_title' => [
                'title' => '欢迎使用企业微信',
                'desc' => '您的好友正在邀请您加入企业微信',
            ],
            'emphasis_content' => [
                'title' => '100',
                'desc' => '数据含义',
            ],
            'sub_title_text' => '下载企业微信还能抢红包！',
            'horizontal_content_list' => [
                0 => [
                    'keyname' => '邀请人',
                    'value' => '张三',
                ],
                1 => [
                    'keyname' => '企微官网',
                    'value' => '点击访问',
                    'type' => 1,
                    'url' => 'https://work.weixin.qq.com/?from=openApi',
                ]
            ],
            'jump_list' => [
                0 => [
                    'type' => 1,
                    'url' => 'https://work.weixin.qq.com/?from=openApi',
                    'title' => '企业微信官网',
                ]
            ],
            'card_action' => [
                'type' => 1,
                'url' => 'https://work.weixin.qq.com/?from=openApi',
                'appid' => 'APPID',
                'pagepath' => 'PAGEPATH',
            ],
        ])->send();
```
---
### 钉钉
>[钉钉-自定义机器人接入](https://developers.dingtalk.com/document/robots/custom-robot-access)
**文本**
``` php 
$robot->text('hello world', ['atMobiles' => ['180xxxxxx'],'atUserIds'=>['user123'],'isAtAll'=>false])->send();
```
**Markdown**
```php
TODO 
```



## Change log

[comment]: <> (Please see the [changelog]&#40;changelog.md&#41; for more information on what has changed recently.)

[comment]: <> (## Testing)

[comment]: <> (``` bash)

[comment]: <> ($ composer test)

[comment]: <> (```)

[comment]: <> (## Contributing)

[comment]: <> (Please see [contributing.md]&#40;contributing.md&#41; for details and a todolist.)

## Security

If you discover any security related issues, please email ymlluo@gmail.com instead of using the issue tracker.

[comment]: <> (## Credits)

[comment]: <> (- [Author Name][link-author])

[comment]: <> (- [All Contributors][link-contributors])

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/ymlluo/grouprobot.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ymlluo/grouprobot.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/ymlluo/grouprobot/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/ymlluo/grouprobot
[link-downloads]: https://packagist.org/packages/ymlluo/grouprobot
[link-travis]: https://travis-ci.org/ymlluo/grouprobot
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/ymlluo
[link-contributors]: ../../contributors
