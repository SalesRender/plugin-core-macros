<?php


namespace Leadvertex\Plugin\Handler\Factories;


use Leadvertex\Plugin\Handler\Commands\BackgroundCommand;
use Leadvertex\Plugin\Handler\Commands\CleanUpCommand;
use Leadvertex\Plugin\Handler\Controllers\PluginController;
use Leadvertex\Plugin\Handler\Controllers\OverviewController;
use RuntimeException;
use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Symfony\Component\Console\Application;

class AppFactory
{

    public static function web(): App
    {
        static::config();
        $app = \Slim\Factory\AppFactory::create();
        $app->addErrorMiddleware(constant('LV_PLUGIN_DEBUG'), true, true);

        $pattern = '/{plugin:[a-zA-Z][a-zA-Z\d_]*}';

        $app->get('/', OverviewController::class . ':index');
        $app->get($pattern, OverviewController::class . ':handler');

        $app->post("{$pattern}/load", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response, $args);
            return $controller->loadSettingsForm();
        });
        $app->post("{$pattern}/load/options", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response, $args);
            return $controller->loadHandleForm();
        });
        $app->post("{$pattern}/validate", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response, $args);
            return $controller->check();
        });
        $app->post("{$pattern}/handle", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response, $args);
            return $controller->handle();
        });

        return $app;
    }

    public static function console(): Application
    {
        static::config();
        $app = new Application();
        $runtimeDir = constant('LV_PLUGIN_DIR_RUNTIME');
        $outputDir = constant('LV_PLUGIN_DIR_PUBLIC');
        $app->add(new CleanUpCommand([$runtimeDir, $outputDir]));
        $app->add(new BackgroundCommand($runtimeDir, $outputDir));
        return $app;
    }

    protected static function config()
    {
        $constants = [
            'LV_PLUGIN_DIR_RUNTIME',
            'LV_PLUGIN_DIR_PUBLIC',
            'LV_PLUGIN_URL_PUBLIC',
            'LV_PLUGIN_CONSOLE_SCRIPT',
            'LV_PLUGIN_DEBUG',
        ];

        foreach ($constants as $constant) {
            if (!defined($constant)) {
                throw new RuntimeException("Constant {$constant} is not defined");
            }
        }
    }
}