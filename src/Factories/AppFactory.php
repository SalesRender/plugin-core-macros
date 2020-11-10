<?php
namespace Leadvertex\Plugin\Core\Macros\Factories;

use Dotenv\Dotenv;

use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Leadvertex\Plugin\Components\Batch\Commands\BackgroundCommand;
use Leadvertex\Plugin\Components\Batch\Commands\QueueCommand;
use Leadvertex\Plugin\Components\Db\Commands\CreateTableAutoCommand;
use Leadvertex\Plugin\Components\Db\Commands\CreateTableManualCommand;
use Leadvertex\Plugin\Components\Db\Commands\TableCleanerCommand;
use Leadvertex\Plugin\Components\DirectoryCleaner\DirectoryCleanerCommand;
use Leadvertex\Plugin\Components\Translations\Commands\LangAddCommand;
use Leadvertex\Plugin\Components\Translations\Commands\LangUpdateCommand;
use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Core\Macros\Controllers\PluginController;
use Leadvertex\Plugin\Core\Macros\MacrosPlugin;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Symfony\Component\Console\Application;
use XAKEPEHOK\Path\Path;

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

        $env = Dotenv::create($repository, (string) Path::root());
        $env->load();

        $env->required('LV_PLUGIN_PHP_BINARY')->notEmpty();
        $env->required('LV_PLUGIN_DEBUG')->isBoolean();
        $env->required('LV_PLUGIN_QUEUE_LIMIT')->notEmpty()->isInteger();
        $env->required('LV_PLUGIN_SELF_URI')->notEmpty();
        $env->required('LV_PLUGIN_SELF_TYPE')->notEmpty();
        $env->required('LV_PLUGIN_COMPONENT_REGISTRATION_SCHEME')->notEmpty();
        $env->required('LV_PLUGIN_COMPONENT_REGISTRATION_HOSTNAME')->notEmpty();

        $_ENV['LV_PLUGIN_SELF_URI'] = rtrim($_ENV['LV_PLUGIN_SELF_URI'], '/') . '/';

        $class = '\Leadvertex\Plugin\Instance\Macros\Plugin';
        $lang = class_exists($class) ? call_user_func([$class, 'getDefaultLanguage']) : 'en_US';
        Translator::config($lang);
    }

    public function web(): App
    {
        $app = \Slim\Factory\AppFactory::create();

        $errorMiddleware = $app->addErrorMiddleware($_ENV['LV_PLUGIN_DEBUG'] ?? false, true, true);
        $errorHandler = $errorMiddleware->getDefaultErrorHandler();
        $errorHandler->forceContentType('application/json');

        $app->options('/{routes:.+}', function ($request, $response, $args) {
            return $response;
        });

        $app->add(function (Request $request, RequestHandlerInterface $handler) {
            /** @var Response $response */
            $response = $handler->handle($request);
            return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, OPTIONS');
        });

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

        $app->get("/forms/batch/{number:[\d]+}", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response);
            return $controller->getBatchForm($args['number']);
        });

        $app->get("/data/settings", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->getSettingsData();
        });

        $app->put("/data/settings", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->setSettingsData();
        });

        $app->put("/data/batch/{number:[\d]+}", function (Request $request, Response $response, array $args) {
            $controller = new PluginController($request, $response);
            return $controller->setBatchData($args['number']);
        });

        $app->post("/batch/prepare", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->batchPrepare();
        });

        $app->post("/batch/run", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->run();
        });

        $app->get("/process", function (Request $request, Response $response) {
            $controller = new PluginController($request, $response);
            return $controller->process();
        });

        $app->get("/robots.txt", function (Request $request, Response $response) {
            $response->getBody()->write("User-agent: *\nDisallow: /");
            return $response;
        });

        $app->setBasePath((function () {
            return rtrim(parse_url($_ENV['LV_PLUGIN_SELF_URI'], PHP_URL_PATH), '/');
        })());

        return $app;
    }

    public function console(): Application
    {
        $app = new Application();

        $app->add(new QueueCommand('run'));
        $app->add(new BackgroundCommand('run', MacrosPlugin::getInstance()->handler()));

        $app->add(new DirectoryCleanerCommand());

        $app->add(new CreateTableAutoCommand());
        $app->add(new CreateTableManualCommand());
        $app->add(new TableCleanerCommand());

        $app->add(new LangAddCommand());
        $app->add(new LangUpdateCommand());

        return $app;
    }
}