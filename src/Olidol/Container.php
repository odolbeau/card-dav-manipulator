<?php

namespace Olidol;

use Pimple\Container as BaseContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Olidol\DAV\Manipulator\Merger;
use Olidol\DAV\Manipulator\DuplicateFinder;
use Olidol\DAV\Manipulator\Repairer;

class Container extends BaseContainer
{
    public function __construct()
    {
        parent::__construct([
            'config.path' => getenv('CARDDAV_CONFIG_PATH') ?: getenv('HOME').'/.config/card-dav-manipulator',

            'client.factory' => function ($c) {
                return new ClientFactory($c['config']);
            },

            'config' => function ($c) {
                return new Config($c['config.path']);
            },

            'merger' => function ($c) {
                return new Merger($c['repairer']);
            },

            'dispatcher' => function ($c) {
                return new EventDispatcher();
            },

            'duplicate_finder' => function ($c) {
                return new DuplicateFinder();
            },

            'repairer' => function ($c) {
                return new Repairer();
            },
        ]);
    }
}
