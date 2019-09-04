<?php
namespace Leadvertex\Plugin\Handler\Factories;

use Leadvertex\Plugin\Handler\Commands\BackgroundCommand;
use Leadvertex\Plugin\Handler\Commands\CleanUpCommand;
use Leadvertex\Plugin\Handler\Controllers\PluginController;
use RuntimeException;
use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Symfony\Component\Console\Application;
use Webmozart\PathUtil\Path;

class AppFactory
{

    public function __construct()
    {
        defined('LV_PLUGIN_DEBUG') or define('LV_PLUGIN_DEBUG', false);

        $constants = [
            'LV_PLUGIN_DIR',
            'LV_PLUGIN_URL',
        ];

        foreach ($constants as $constant) {
            if (!defined($constant)) {
                throw new RuntimeException("Constant {$constant} is not defined");
            }
        }

        define('LV_PLUGIN_DIR_RUNTIME', Path::canonicalize(constant('LV_PLUGIN_DIR') . '/runtime'));
        define('LV_PLUGIN_DIR_OUTPUT', Path::canonicalize(constant('LV_PLUGIN_DIR') . '/public/output'));
        define('LV_PLUGIN_URL_OUTPUT', Path::canonicalize(constant('LV_PLUGIN_URL') . '/output'));
    }

    public function web(): App
    {
        $app = \Slim\Factory\AppFactory::create();
        $app->addErrorMiddleware(constant('LV_PLUGIN_DEBUG'), true, true);

        $pattern = '/{plugin:[a-zA-Z][a-zA-Z\d_]*}';

        $app->post("{$pattern}/load", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response, $args);
            return $controller->load();
        });

        $app->post("{$pattern}/load/settings", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response, $args);
            return $controller->loadSettingsForm();
        });

        $app->post("{$pattern}/load/options", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response, $args);
            return $controller->loadOptionsForm();
        });

        $app->post("{$pattern}/validate", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response, $args);
            return $controller->validateSettingsForm();
        });

        $app->post("{$pattern}/handle", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response, $args);
            return $controller->handle();
        });

        $app->post("/robots.txt", function (Request $request, Response $response) {
            $response->getBody()->write("User-agent: *\nDisallow: /");
            return $response;
        });

        return $app;
    }

    public function console(): Application
    {
        $app = new Application();
        $runtimeDir = constant('LV_PLUGIN_DIR_RUNTIME');
        $outputDir = Path::canonicalize(constant('LV_PLUGIN_DIR_PUBLIC') . '/output');
        $app->add(new CleanUpCommand([$runtimeDir, $outputDir]));
        $app->add(new BackgroundCommand($runtimeDir, $outputDir));
        return $app;
    }
}