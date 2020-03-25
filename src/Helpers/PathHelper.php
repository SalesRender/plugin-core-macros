<?php
/**
 * Created for plugin-core
 * Datetime: 20.02.2020 16:53
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Core\Macros\Helpers;


use Composer\Autoload\ClassLoader;
use ReflectionClass;
use XAKEPEHOK\Path\Path;

class PathHelper
{

    private static $root;

    public static function getRoot(): Path
    {
        if (self::$root === null) {
            $reflection = new ReflectionClass(ClassLoader::class);
            self::$root = (new Path($reflection->getFileName()))->up()->up()->up();
        }
        return self::$root;
    }

    public static function getTemp(): Path
    {
        return self::getRoot()->down('temp');
    }

    public static function getPublic(): Path
    {
        return self::getRoot()->down('public');
    }

    public static function getPublicOutput(): Path
    {
        return self::getRoot()->down('public')->down('output');
    }

    public static function getPublicUpload(): Path
    {
        return self::getRoot()->down('public')->down('uploaded');
    }

}