<?php

namespace Olidol;

use Pimple\Container as BaseContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Container extends BaseContainer
{
    public function __construct()
    {
        parent::__construct([
            'config.path' => getenv('CARDDAV_CONFIG_PATH') ?: getenv('HOME').'/.config/card-dav-manipulator',

            'config' => function ($c) {
                return new Config($c['config.path']);
            },

            'dispatcher' => function ($c) {
                return new EventDispatcher();
            }
        ]);
    }
}
