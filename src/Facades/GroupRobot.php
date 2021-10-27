<?php

namespace Ymlluo\GroupRobot\Facades;

use Illuminate\Support\Facades\Facade;

class GroupRobot extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'grouprobot';
    }
}
