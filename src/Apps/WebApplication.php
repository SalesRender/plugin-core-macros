<?php


namespace Leadvertex\Plugin\Export\Core\Apps;


use HaydenPierce\ClassFinder\ClassFinder;
use Leadvertex\Plugin\Export\Core\Components\ApiParams;
use Leadvertex\Plugin\Export\Core\Components\BatchParams;
use Leadvertex\Plugin\Export\Core\Components\ChunkedIds;
use Leadvertex\Plugin\Export\Core\Components\DeferredRunner;
use Leadvertex\Plugin\Export\Core\Components\GenerateParams;
use Leadvertex\Plugin\Export\Core\Components\StoredConfig;
use Leadvertex\Plugin\Export\Core\Formatter\Type;
use Leadvertex\Plugin\Export\Core\Formatter\FormatterInterface;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Webmozart\PathUtil\Path;

/**
 * Class WebApplication
 * @package Leadvertex\Plugin\Export\Core\Apps
 *
 * @property bool $debugMode
 * @property string $runtimeDir
 * @property string $publicDir
 * @property string $publicUrl
 * @property string $consoleScript
 */
class WebApplication extends App
{

    /**
     * WebApplication constructor.
     * @param string $runtimeDir
     * @param string $publicDir
     * @param string $publicUrl
     * @param string $consoleScript
     * @param bool $debug //DO NOT USE IN PRODUCTION! It run generation at http request, not by background console task
     */
    public function __construct(
        string $runtimeDir,
        string $publicDir,
        string $publicUrl,
        string $consoleScript,
        bool $debug = false
    )
    {
        parent::__construct([
            'settings' => [
                'displayErrorDetails' => $debug,
                'addContentLengthHeader' => true,
            ],
            'debugMode' => $debug,
            'runtimeDir' => $runtimeDir,
            'publicDir' => $publicDir,
            'publicUrl' => $publicUrl,
            'consoleScript' => $consoleScript,
        ]);

        //TODO prettify output
        $this->get('/', function (Request $request, Response $response, $args) {
            /** @var FormatterInterface[] $classes */
            $classes = ClassFinder::getClassesInNamespace('Leadvertex\Plugin\Export\Format', ClassFinder::RECURSIVE_MODE);

            $data = [];
            foreach ($classes as $classname) {
                if (!is_a($classname, FormatterInterface::class, true)) {
                    continue;
                }

                $name = substr(strrchr($classname, "\\"), 1);
                $data[$name] = [
                    'name' => $classname::getName()->getTranslations(),
                    'description' => $classname::getDescription()->getTranslations(),
                ];
            }

            return $response->withJson($data);
        });

        //TODO prettify output
        $this->get('/{formatter:[a-zA-Z][a-zA-Z\d_]*}', function (Request $request, Response $response, $args) {
            $format = $args['formatter'];

            /** @var FormatterInterface $classname */
            $classname = "\Leadvertex\Plugin\Export\Format\\{$format}\\{$format}";

            return $response->withJson([
                'name' => $classname::getName()->getTranslations(),
                'description' => $classname::getDescription()->getTranslations(),
            ]);
        });

        $this->rpc('CONFIG', function (Request $request, Response $response, $args) {
            $format = $args['formatter'];

            $apiParams = new ApiParams(
                $request->getParsedBodyParam('api')['token'],
                $request->getParsedBodyParam('api')['endpointUrl']
            );

            $classname = "\Leadvertex\Plugin\Export\Format\\{$format}\\{$format}";
            /** @var FormatterInterface $formatter */
            $formatter = new $classname($apiParams, $this->runtimeDir, $this->publicDir, $this->publicUrl);
            return $response->withJson(
                $formatter->getScheme()->toArray(),
                200
            );
        });

        $this->rpc('VALIDATE', function (Request $request, Response $response, $args) {
            $format = $args['formatter'];

            $apiParams = new ApiParams(
                $request->getParsedBodyParam('api')['token'],
                $request->getParsedBodyParam('api')['endpointUrl']
            );

            $config = new StoredConfig(
                $request->getParsedBodyParam('config')
            );

            $classname = "\Leadvertex\Plugin\Export\Format\\{$format}\\{$format}";
            /** @var FormatterInterface $formatter */
            $formatter = new $classname($apiParams, $this->runtimeDir, $this->publicDir, $this->publicUrl);

            $type = new Type($request->getParsedBodyParam('type'));

            if (!$type->isEquals($formatter->getScheme()->getType())) {
                return $response->withJson(['valid' => false],405);
            }

            if (!$formatter->isConfigValid($config)) {
                return $response->withJson(['valid' => false],400);
            }

            return $response->withJson(['valid' => true],200);
        });

        $this->rpc('GENERATE', function (Request $request, Response $response, $args) {

            $format = $args['formatter'];
            $classname = "\Leadvertex\Plugin\Export\Format\\{$format}\\{$format}";

            /** @var FormatterInterface $formatter */
            $formatter = new $classname(
                new ApiParams(
                    $request->getParsedBodyParam('api')['token'],
                    $request->getParsedBodyParam('api')['endpointUrl']
                ),
                $this->runtimeDir,
                $this->publicDir,
                $this->publicUrl
            );

            $batchToken = $request->getParsedBodyParam('batch')['token'];
            $params = new GenerateParams(
                new Type($request->getParsedBodyParam('type')),
                new StoredConfig($request->getParsedBodyParam('config')),
                new BatchParams(
                    $batchToken,
                    $request->getParsedBodyParam('batch')['progressWebhookUrl'],
                    $request->getParsedBodyParam('batch')['resultWebhookUrl']
                ),
                new ChunkedIds($request->getParsedBodyParam('ids'))
            );

            if ($this->debugMode) {
                $formatter->generate($params);
                return $response->withJson(['result' => true],200);
            }

            $tokensDir = Path::canonicalize("{$this->runtimeDir}/tokens");
            $handler = new DeferredRunner($tokensDir);
            $handler->prepend($formatter, $params);

            $command = "php {$this->consoleScript} app:background {$batchToken}";

            $isWindowsOS = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            if ($isWindowsOS) {
                pclose(popen("start /B {$command}", "r"));
            } else {
                exec("{$command} > /dev/null &");
            }

            return $response->withJson(['result' => true],200);
        });
    }

    private function rpc(string $method, callable $callable)
    {
        $this->map([$method], '/{formatter:[a-zA-Z][a-zA-Z\d_]*}', $callable);
    }

}