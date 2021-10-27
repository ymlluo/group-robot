# 群组机器人

[comment]: <> ([![Latest Version on Packagist][ico-version]][link-packagist])

[comment]: <> ([![Total Downloads][ico-downloads]][link-downloads])

[comment]: <> ([![Build Status][ico-travis]][link-travis])

[comment]: <> ([![StyleCI][ico-styleci]][link-styleci])

[comment]: <> (This is where your description should go. Take a look at [contributing.md]&#40;contributing.md&#41; to see a to do list.)

## Installation

Via Composer

``` bash
$ composer require ymlluo/group-robot
```

## Usage

```php
$robot = new \Ymlluo\GroupRobot\GroupRobot('wechat');
$robot->setWebhook('http://xxxx')->text("hello world")->send();
$robot->setWebhook('http://xxxx')->image("/tmp/1.png")->send();
$robot->setWebhook('http://xxxx')->image("https://www.baidu.com/1.png")->send();
$robot->setWebhook('http://xxxx')->file("/tmp/1.zip")->send();


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
