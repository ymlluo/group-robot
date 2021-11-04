# 群机器人

[comment]: <> ([![Latest Version on Packagist][ico-version]][link-packagist])

[comment]: <> ([![Total Downloads][ico-downloads]][link-downloads])

[comment]: <> ([![Build Status][ico-travis]][link-travis])

[comment]: <> ([![StyleCI][ico-styleci]][link-styleci])

## 通用消息类型支持情况

| APP   | 名称 | 文本 | MD | 图片 | 文件 | 图文 | 卡片 |
| ------ | ---- | ------ | ------ | ------ | ------ | ------ | ------ |
| 企业微信 | wechat  | √ | √ | √ |√ |  √ | √ |
| 钉钉 | dingtalk  | √ | √ | √ |√ |  √ | √ |
| 飞书 | feishu  | √ | √ | × | √ | × | √ |

Table of Contents
=================

* [群机器人](#群机器人)
    * [通用消息类型支持情况](#通用消息类型支持情况)
* [Table of Contents](#table-of-contents)
* [安装](#安装)
* [使用说明](#使用说明)
    * [企业微信](#企业微信)
        * [初始化](#初始化)
        * [原生消息](#原生消息)
        * [文本](#文本)
        * [Markdown](#markdown)
        * [网络图片](#网络图片)
        * [本地图片](#本地图片)
        * [网络文件](#网络文件)
        * [本地文件](#本地文件)
        * [图文](#图文)
        * [通用卡片](#通用卡片)
        * [模版卡片](#模版卡片)
    * [钉钉](#钉钉)
        * [初始化](#初始化-1)
        * [原生消息](#原生消息-1)
        * [文本](#文本-1)
        * [Markdown](#markdown-1)
        * [图片](#图片)
        * [文件](#文件)
        * [单条图文](#单条图文)
        * [链接消息](#链接消息)
        * [多条图文](#多条图文)
        * [FeedCard](#feedcard)
        * [通用卡片](#通用卡片-1)
        * [actionCard](#actioncard)
    * [飞书](#飞书)
        * [初始化](#初始化-2)
        * [原生消息](#原生消息-2)
        * [文本](#文本-2)
        * [Markdown](#markdown-2)
        * [图片](#图片-1)
        * [图文消息](#图文消息)
        * [文件](#文件-1)
        * [通用卡片](#通用卡片-2)
        * [消息卡片](#消息卡片)
        * [富文本](#富文本)
    * [Laravel 支持](#laravel-支持)
    * [进阶用法](#进阶用法)
        * [使用「队列」同时发送多条消息](#使用队列同时发送多条消息)
        * [使用「抄送」功能向多个平台发送消息](#使用抄送功能向多个平台发送消息)
        * [定义平台](#定义平台)
    * [Contributing](#contributing)
    * [Security](#security)
    * [License](#license)

Created by [gh-md-toc](https://github.com/ekalinin/github-markdown-toc)


# 安装

Via Composer

``` bash
$ composer require ymlluo/group-robot
```

# 使用说明

** <font color='red'>特别特别要注意：一定要保护好机器人的webhook地址，避免泄漏！</font>**

---

## 企业微信

> [企业微信-群机器人配置说明](https://work.weixin.qq.com/api/doc/90000/90136/91770)

### 初始化

```php 

```php 
//初始化
$webhook = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=693a91f6-7xxx-4bc4-97a0-0ec2sifa5aaa';

/**
 * GroupRobot constructor.
 * @param string $platform 发送平台，wechat:企业微信,dingtalk:钉钉,feishu:飞书
 * @param string $webhook webhook 地址
 * @param string $secret 秘钥,加密方式加签时必填，企业微信不支持这个参数
 * @param string $alias 别名，多渠道发送时方便区分结果
 */
$robot = new GroupRobot('wechat', $webhook, '', 'robot_wx_1');
```

### 原生消息

```php 
$robot->raw(['msgtype'=>'text','text'=>['content'=>'hello world!']])->send();
```

### 文本
* 支持 @全部、@用户、@手机号
* 文本内容，单条最多2048个字节。
* 发送内容超过 2048 个字节，会分成多条发送（使用 \n 分割）

``` php 
$robot->text('hello world')->send();
```

```php 
//@全部
$robot->text('hello world')->atAll()->send();
```

```php 
//@用户
$robot->text('hello world')->atUsers(["wangqing"])->send();
```

```php 
//@手机号
$robot->text('hello world')->atMobiles(["13800001111"])->send();
```

### Markdown
* 仅支持 @用户
* markdown内容，最长不超过 4096 个字节。
* 发送内容超过 4096 个字节，会拆分成多条发送（使用 \n 分割）

```php
 $robot->markdown("实时新增用户反馈<font color=\"warning\">132例</font>，请相关同事注意。")->send();
```
```php
 $robot->markdown("### 用户反馈，请相关同事注意。")->atUsers(["wangqing"])->send();
```

### 网络图片
* 程序会下载图片到本地后再上传到微信服务器

```php
$robot->image('http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png')->send();
```
### 本地图片
```php 
$robot->image('/tmp/images/test_pic_msg1.png')->send();
```

### 网络文件
* 程序会下载文件到本地后再上传到微信服务器

```php 
$robot->file('http://www.gov.cn/zhengce/pdfFile/2021_PDF.pdf','政府信息公开目录.pdf')->send();
```
### 本地文件
```php 
$robot->file('/tmp/pdfFile/2021_PDF.pdf','政府信息公开目录.pdf')->send();
```

### 图文

```php 
//单条图文
$robot->news([
  'title' => '中秋节礼品领取',
  'description' => '今年中秋节公司有豪礼相送',
  'url' => 'www.qq.com',
  'picurl' => 'http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png'
  ])->send();
  
```

```php 
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

### 通用卡片
* 基于模板卡片 - news_notice 构建

```php
$robot->card(
  "Apple Store 的前身",//一级标题，建议不超过26个字
  "乔布斯 20 年前想打造的苹果咖啡厅 \nApple Store 的设计正从原来满满的科技感走向生活化，而其生活化的走向其实可以追溯到 20 年前苹果一个建立咖啡馆的计划",//标题辅助信息，建议不超过30个字
  "https://gw.alicdn.com/tfs/TB1ut3xxbsrBKNjSZFpXXcXhFXa-846-786.png",//封面图片
  "https://www.qq.com/",//链接跳转的url
  [['title' => '钉钉', 'url' => 'https://www.dingtalk.com/'], ['title' => '百度', 'url' => 'https://www.baidu.com/']]
)->send();
```

### 模版卡片

```php 
//文本通知模版卡片
$robot->template_card([
            'card_type' => 'text_notice',
            'source' => [
                'icon_url' => 'https://wework.qpic.cn/wwpic/252813_jOfDHtcISzuodLa_1629280209/0',
                'desc' => '企业微信',
            ],
            'main_title' => [
              [
                'title' => '欢迎使用企业微信',
                'desc' => '您的好友正在邀请您加入企业微信',
              ]
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

```php 
//图文展示模版卡片
$robot->template_card([
            'card_type' => 'news_notice',
            'source' => [
                'icon_url' => 'https://wework.qpic.cn/wwpic/252813_jOfDHtcISzuodLa_1629280209/0',
                'desc' => '企业微信',
            ],
            'main_title' => [
                'title' => '欢迎使用企业微信',
                'desc' => '您的好友正在邀请您加入企业微信',
            ],
            'card_image' => [
                'url' => 'https://wework.qpic.cn/wwpic/354393_4zpkKXd7SrGMvfg_1629280616/0',
                'aspect_ratio' => 2.25,
            ],
            'vertical_content_list' =>[
              'title'=>'惊喜红包等你来拿',
              'desc'=>'下载企业微信还能抢红包！'
            ],
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

## 钉钉

> [钉钉-自定义机器人接入](https://developers.dingtalk.com/document/robots/custom-robot-access)

### 初始化 

```php


//初始化并设置webhook
$webhook = 'https://oapi.dingtalk.com/robot/send?access_token=XXXXXX';
$token = 'xxx';

/**
 * @param string $platform 发送平台，wechat:企业微信,dingtalk:钉钉,feishu:飞书
 * @param string $webhook webhook 地址
 * @param string $secret 秘钥,加密方式加签时必填，企业微信不支持这个参数
 * @param string $alias 别名，多渠道发送时方便区分结果
 */
$robot = new GroupRobot('dingtalk',$webhook,$token,'d1');
```


### 原生消息
```php 
$robot->raw(['msgtype'=>'text','text'=>['content'=>'hello world!']])->send();
```

### 文本
* 支持 @全部、@用户、@手机号
``` php 
$robot->text('hello world')->send();
```

```php 
//@全部
$robot->text('hello world')->atAll()->send();
```

```php 
//@用户 
$robot->text('hello world')->atUsers(["user123"])->send();
```

```php 
//@手机号
$robot->text('hello world')->atMobiles(["13800001111"])->send();
```

### Markdown
* 支持 @全部、@用户、@手机号
* 目前只支持markdown语法的子集
* 参考文档： [markdown语法](https://developers.dingtalk.com/document/robots/custom-robot-access/title-72m-8ag-pqw)
```php
$robot->markdown("#### 杭州天气 \n > 9度，西北风1级，空气良89，相对温度73%\n > ![screenshot](https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png) \n", '杭州天气')->send();
```
```php
$robot->markdown("at 全部用户 \n", 'at')->atAll()->send();
```
```php
$robot->markdown("at 用户 \n", 'at')->atUsers(["user123"])->send();
```
```php
$robot->markdown("at 手机 \n", 'at')->atMobiles(["13800001111"])->send();
```

### 图片
* 基于 Markdown 构建
* 仅支持网络图片
* 不支持本地图片
``` php
$robot->image("https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png")->send();
```

### 文件
* 基于 Markdown 构建
* 仅支持网络文件
* 不支持本地文件
``` php
$robot->file("http://h10032.www1.hp.com/ctg/Manual/c05440029.pdf", "HP 2600打印机说明书.pdf")->send();
```

### 单条图文
* 基于链接消息 link 构建
```php 
//单条图文
$robot->news([
  'title' => '中秋节礼品领取',
  'description' => '今年中秋节公司有豪礼相送',
  'url' => 'https://developers.dingtalk.com/document/robots/robot-overview',
  'picurl' => 'http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png'
])->send();
```

### 链接消息

```php 


//等同于
$robot->link([
  'title' => '中秋节礼品领取',
  'text' => '今年中秋节公司有豪礼相送',
  'messageUrl' => 'https://developers.dingtalk.com/document/robots/robot-overview',
  'picUrl' => 'http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png'
])->send();
```

### 多条图文 
* 基于 FeedCard 构建

```php 
$robot->news([
  [
  'title' => '中秋节礼品领取',
  'url' => 'https://developers.dingtalk.com/document/robots/robot-overview',
  'picurl' => 'http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png'
  ],
  [
  'title' => '杭州天气',
  'url' => 'https://developers.dingtalk.com/document/robots/robot-overview',
  'picurl' => 'https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png'
  ],
])->send();
```

### FeedCard

```php 
$robot->feedCard([
  [
  'title' => '中秋节礼品领取',
  'description' => '今年中秋节公司有豪礼相送',
  'messageURL' => 'https://developers.dingtalk.com/document/robots/robot-overview',
  'picURL' => 'http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png'
  ],
  [
  'title' => '杭州天气',
  'messageURL' => 'https://developers.dingtalk.com/document/robots/robot-overview',//注意是messageURL 不是messageUrl
  'picURL' => 'https://img.alicdn.com/tfs/TB1NwmBEL9TBuNjy1zbXXXpepXa-2400-1218.png' //注意是picURL 不是picUrl
  ],
])->send();
```

### 通用卡片 
* 基于 ActionCard 构建

```php 
//Card 单个按钮
  $robot->card(
   "乔布斯 20 年前想打造一间苹果咖啡厅，而它正是 Apple Store 的前身",
   "### 乔布斯 20 年前想打造的苹果咖啡厅 \nApple Store 的设计正从原来满满的科技感走向生活化，而其生活化的走向其实可以追溯到 20 年前苹果一个建立咖啡馆的计划",
   "https://gw.alicdn.com/tfs/TB1ut3xxbsrBKNjSZFpXXcXhFXa-846-786.png",
   "https://www.dingtalk.com/",
   ['title'=>'阅读全文','url'=>'https://www.dingtalk.com/']
 )->send();
```
```php 
//Card 多个按钮
  $robot->card(
    "乔布斯 20 年前想打造一间苹果咖啡厅，而它正是 Apple Store 的前身",//标题
    "### 乔布斯 20 年前想打造的苹果咖啡厅",//描述
    "https://gw.alicdn.com/tfs/TB1ut3xxbsrBKNjSZFpXXcXhFXa-846-786.png",//封面图
    "https://www.dingtalk.com/",//跳转地址
    [['title' => '内容不错', 'url' => 'https://www.dingtalk.com/'], ['title' => '不感兴趣', 'url' => 'https://www.dingtalk.com/']],//按钮
    ['btnOrientation' => "1"] //非必填，按钮排列
)->send();
```

###  actionCard 

```php 
//单个按钮
  $robot->actionCard([
   'title'=>'乔布斯 20 年前想打造一间苹果咖啡厅，而它正是 Apple Store 的前身',
   'text'=>"![screenshot](https://gw.alicdn.com/tfs/TB1ut3xxbsrBKNjSZFpXXcXhFXa-846-786.png) \n### 乔布斯 20 年前想打造的苹果咖啡厅 \nApple Store 的设计正从原来满满的科技感走向生活化，而其生活化的走向其实可以追溯到 20 年前苹果一个建立咖啡馆的计划",
   'btnOrientation'=>'0',
   'singleTitle'=>'阅读全文',
   'singleURL'=>'https://www.dingtalk.com/'
 ])->send();
```

```php 
//多个按钮
  $robot->actionCard([
    'title'=>'乔布斯 20 年前想打造一间苹果咖啡厅，而它正是 Apple Store 的前身',
    'text'=>"![screenshot](https://gw.alicdn.com/tfs/TB1ut3xxbsrBKNjSZFpXXcXhFXa-846-786.png) \n### 乔布斯 20 年前想打造的苹果咖啡厅 \nApple Store 的设计正从原来满满的科技感走向生活化，而其生活化的走向其实可以追溯到 20 年前苹果一个建立咖啡馆的计划",
     'btnOrientation'=>'0',
     'btns'=>[
       [
         'title'=>'内容不错',
         'actionURL'=>'https://www.dingtalk.com/'
       ],
       [
         'title'=>'不感兴趣',
         'actionURL'=>'https://www.dingtalk.com/'
       ]
     ]
 ])->send();

```


## 飞书

> [飞书-自定义机器人指南](https://open.feishu.cn/document/ukTMukTMukTM/ucTM5YjL3ETO24yNxkjN)

### 初始化 

```php


//初始化并设置webhook
$webhook = 'https://open.feishu.cn/open-apis/bot/v2/hook/XXXXXX';
$token = 'xxx';
/**
 * @param string $platform 发送平台，wechat:企业微信,dingtalk:钉钉,feishu:飞书
 * @param string $webhook webhook 地址
 * @param string $secret 秘钥,加密方式加签时必填
 * @param string $alias 别名，多渠道发送时方便区分结果
 */
$robot = new GroupRobot('feishu',$webhook,$token,'feishu-1');
```


### 原生消息 
```php 
$robot->raw(['msg_type'=>'text','content'=>['text'=>'hello world!']])->send();
```

### 文本
* 仅支持 @全部
``` php 
$robot->text('hello world')->atAll()->send();
```

### Markdown
* 基于卡片消息构建
* 无法使用与文本格式无关的markdown标签（比如图片、分割线）
* 目前只支持 markdown 语法的子集，支持的有限元素。
* 文档参考： [消息卡片构造卡片内容Markdown模块](https://open.feishu.cn/document/ukTMukTMukTM/uADOwUjLwgDM14CM4ATN)
* 仅支持 @全部

```php
$robot->markdown("#### 杭州天气 \n > 9度，西北风1级，空气良89，相对温度73%\n", '杭州天气')->atAll()->send();

```
### 图片
*不支持*

### 图文消息
*不支持*


### 文件
* 基于卡片消息 Markdown 构建
* 仅支持网络文件，不支持本地文件
``` php
$robot->file("http://h10032.www1.hp.com/ctg/Manual/c05440029.pdf", "HP 2600打印机说明书.pdf")->send();
```
### 通用卡片

* card 方法基于 interactive 构建
* 不支持设置图片
* 模板样式参考：[卡片支持的模板](https://open.feishu.cn/document/ukTMukTMukTM/ukTNwUjL5UDM14SO1ATN )

```php 
//Card 单个按钮
  $robot->card(
   "乔布斯 20 年前想打造一间苹果咖啡厅，而它正是 Apple Store 的前身",//title
   "### 乔布斯 20 年前想打造的苹果咖啡厅",//描述
   "",//封面图片，不支持
   "https://www.dingtalk.com/",//跳转地址
   ['title'=>'阅读全文','url'=>'https://www.dingtalk.com/'],//选填，按钮
   ['template'=>'red'] //模板样式，
 )->send();
 ```
### 消息卡片
```php 
$robot->interactive([
            'header' => [
                'title' => [
                    'content' => '今日旅游推荐',
                    'tag' => 'plain_text'
                ]
            ],
            'elements' => [
                [
                    'tag' => 'div',
                    'text' => [
                        'content' => '**西湖**，位于浙江省杭州市西湖区龙井路1号',
                        'tag' => 'lark_md'
                    ]
                ],
                [
                    'tag' => 'markdown',
                    'content' => "*西湖美景二月天*"
                ],
                [
                    'tag' => 'action',
                    'actions' => [
                        [
                            'tag' => 'button',
                            'text' => [
                                'content' => '更多景点介绍 :玫瑰:',
                                'tag' => 'lark_md'
                            ],
                            'url' => 'https://feishu.cn',
                            'type' => 'default'
                        ]
                    ]
                ]
            ]
        ])->send();

```

### 富文本
* 仅支持 @全部

```php 
$robot->rich([
  'title' => '项目更新',
    'content' => [
    [
      [
        'tag' => 'text',
        'text' => '项目有更新:'
      ], 
      [
        'tag' => 'a',
        'text' => '请查看',
        'href' => 'https://feishu.cn'
      ],
      [
        'tag' => 'at',
        'user_id' => 'all'
      ]
    ]
  ]
])->send();

```
----

##  Laravel 支持

如果在Laravel 中使用，添加下面一行到 config/app.php 中 providers 部分：
> Laravel > 5.5 支持 Package Auto-Discovery 无需手动添加配置
```php 
  Ymlluo\\GroupRobot\\GroupRobotServiceProvider::class
```
**使用**
```php 
$webhook = 'https://oapi.dingtalk.com/robot/send?access_token=xxxx';
$secret = 'xxx';
$results = app('grouprobot')->queue()->text("Hello ")->text('Laravel')->cc('dingtalk',$webhook, $secret, 'ding_1')->send();
dd($result);
```

------
## 进阶用法 

### 使用「队列」同时发送多条消息
* 使用 queue() 方法可以把消息放到队列中，分别发送
```php 
$robot->queue()
  ->text("文本消息1")
  ->text("文本消息2")
  ->markdown("**Markdown**")
  ->file("http://h10032.www1.hp.com/ctg/Manual/c05440029.pdf", "HP 2600打印机说明书.pdf")
  ->send();
```
### 使用「抄送」功能向多个平台发送消息

```php
$robot = new GroupRobot();
$robot->text("开始@".date('Y-m-d H:i:s',time()))
      ->text("结束@".date('Y-m-d H:i:s',time()))
      ->queue()
      ->cc('dingtalk','https://oapi.dingtalk.com/robot/send?access_token==xxxx','xxx','ding1')
      ->cc('wechat','https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=xxxx','','wx_1')
      ->cc('feishu','https://open.feishu.cn/open-apis/bot/v2/hook/xxxx','xxx','feishu1')
      ->send();
```





### 定义平台
> 可以实现 src/Contracts/Platform.php 接口，通过自定义平台发送消息
```php 
<?php

namespace App;

use Ymlluo\GroupRobot\Contracts\Platform;
use Ymlluo\GroupRobot\Notify\BaseNotify;

class CustomerPlatform extends BaseNotify implements Platform
{
    // todo
}

```

```php 
$webhook = 'https://xxx.com/webhook/xxx';
$robot = new GroupRobot();
$custom = new CustomerPlatform(); 
$robot->extendPlatform($custom,$webhook,$secret='',$alias='c1')->text("hello !")->send();
```
------

[comment]: <> (## Change log)

[comment]: <> (Please see the [changelog]&#40;changelog.md&#41; for more information on what has changed recently.)

[comment]: <> (## Testing)

[comment]: <> (``` bash)

[comment]: <> ($ composer test)

[comment]: <> (```)
## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

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
