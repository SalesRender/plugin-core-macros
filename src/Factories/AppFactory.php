<?php
namespace Leadvertex\Plugin\Core\Macros\Factories;

use Dotenv\Dotenv;

use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Leadvertex\Plugin\Components\Db\Commands\CreateTableAutoCommand;
use Leadvertex\Plugin\Components\Db\Commands\CreateTableManualCommand;
use Leadvertex\Plugin\Components\Translations\Commands\LangAddCommand;
use Leadvertex\Plugin\Components\Translations\Commands\LangUpdateCommand;
use Leadvertex\Plugin\Core\Macros\Commands\BackgroundCommand;
use Leadvertex\Plugin\Core\Macros\Commands\DbCleanerCommand;
use Leadvertex\Plugin\Core\Macros\Commands\QueueCommand;
use Leadvertex\Plugin\Core\Macros\Helpers\PathHelper;
use Leadvertex\Plugin\Core\Macros\Commands\DirectoryCleanerCommand;
use Leadvertex\Plugin\Core\Macros\Controllers\PluginController;
use Slim\App;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Symfony\Component\Console\Application;

class AppFactory
{

    public function __construct()
    {
        $repository = RepositoryBuilder::create()
            ->withReaders([new EnvConstAdapter()])
            ->withWriters([new EnvConstAdapter()])
            ->immutable()
            ->make();

        $_ENV['LV_PLUGIN_SELF_TYPE'] = 'MACROS';

        $env = Dotenv::create($repository, (string) PathHelper::getRoot());
        $env->load();

        $env->required('LV_PLUGIN_PHP_BINARY')->notEmpty();
        $env->required('LV_PLUGIN_DEBUG')->isBoolean();
        $env->required('LV_PLUGIN_QUEUE_LIMIT')->notEmpty()->isInteger();
        $env->required('LV_PLUGIN_SELF_URI')->notEmpty();
        $env->required('LV_PLUGIN_SELF_TYPE')->notEmpty();
        $env->required('LV_PLUGIN_COMPONENT_HANDSHAKE_SCHEME')->notEmpty();
        $env->required('LV_PLUGIN_COMPONENT_HANDSHAKE_HOSTNAME')->notEmpty();
    }

    public function web(): App
    {
        $app = \Slim\Factory\AppFactory::create();

        $errorMiddleware = $app->addErrorMiddleware($_ENV['LV_PLUGIN_DEBUG'] ?? false, true, true);
        $errorHandler = $errorMiddleware->getDefaultErrorHandler();
        $errorHandler->forceContentType('application/json');

        $app->get("/info", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->info();
        });

        $app->put("/registration", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->registration();
        });

        $app->post("/upload", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->upload();
        });

        $app->get("/autocomplete/{name:[a-zA-Z\d_\-\.]+}", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response);
            return $controller->autocomplete($args['name']);
        });

        $app->get("/forms/settings", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->getSettingsForm();
        });

        $app->get("/forms/options/{number:[\d]+}", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response);
            return $controller->getRunForm($args['number']);
        });

        $app->get("/data/settings", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->getSettings();
        });

        $app->put("/data/settings", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->setSettings();
        });

        $app->put("/data/options/{number:[\d]+}", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response);
            return $controller->setRunOptions($args['number']);
        });

        $app->post("/run", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->run();
        });

        $app->get("/process", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->process();
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

        $app->add(new DirectoryCleanerCommand());
        $app->add(new DbCleanerCommand());

        $app->add(new QueueCommand());
        $app->add(new BackgroundCommand());

        $app->add(new CreateTableAutoCommand());
        $app->add(new CreateTableManualCommand());

        $app->add(new LangAddCommand());
        $app->add(new LangUpdateCommand());

        return $app;
    }
}