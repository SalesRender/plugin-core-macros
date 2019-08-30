<?php
/**
 * Created for plugin-core
 * Datetime: 05.08.2019 16:10
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Handler\Components;


use Leadvertex\Plugin\Components\ApiClient\ApiClient;
use Leadvertex\Plugin\Handler\PluginInterface;

class PluginFactory
{

    public static function create(string $name, ApiClient $client): PluginInterface
    {
        $classname = "\\Leadvertex\\\Plugin\\Handler\\{$name}\\{$name}";
        return new $classname(
            $client,
            constant('LV_PLUGIN_DIR_RUNTIME'),
            constant('LV_PLUGIN_DIR_PUBLIC'),
            constant('LV_PLUGIN_URL_PUBLIC')
        );
    }

}