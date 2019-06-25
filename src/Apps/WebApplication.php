<?php


namespace Leadvertex\External\Export\Core\Apps;


use Leadvertex\External\Export\Core\Components\ApiParams;
use Leadvertex\External\Export\Core\Components\BatchParams;
use Leadvertex\External\Export\Core\Components\ChunkedIds;
use Leadvertex\External\Export\Core\Components\DeferredRunner;
use Leadvertex\External\Export\Core\Components\GenerateParams;
use Leadvertex\External\Export\Core\Components\StoredConfig;
use Leadvertex\External\Export\Core\Formatter\Type;
use Leadvertex\External\Export\Core\Formatter\FormatterInterface;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Webmozart\PathUtil\Path;

/**
 * Class WebApplication
 * @package Leadvertex\External\Export\Core\Apps
 *
 * @property string $runtimeDir
 * @property string $outputDir
 * @property string $consolePath
 */
class WebApplication extends App
{

    public function __construct(string $runtimeDir, string $outputDir, string $consolePath, bool $debug = false)
    {
        parent::__construct([
            'settings' => [
                'displayErrorDetails' => $debug,
                'addContentLengthHeader' => true,
            ],
            'runtimeDir' => $runtimeDir,
            'outputDir' => $outputDir,
            'consolePath' => $consolePath,
        ]);

        $this->rpc('CONFIG', function (Request $request, Response $response, $args) {
            $format = $args['formatter'];

            $apiParams = new ApiParams(
                $request->getParsedBodyParam('api')['token'],
                $request->getParsedBodyParam('api')['endpointUrl']
            );

            $classname = "\Leadvertex\External\Export\Format\\{$format}\\{$format}";
            /** @var FormatterInterface $formatter */
            $formatter = new $classname($apiParams, $this->runtimeDir, $this->outputDir);
            return $response->withJson(
                $formatter->getScheme()->toArray(),
                200
            );
        });

        $this->rpc('VALIDATE', function (Request $request, Response $response, $args) {
            $formatter = $args['formatter'];

            $apiParams = new ApiParams(
                $request->getParsedBodyParam('api')['token'],
                $request->getParsedBodyParam('api')['endpointUrl']
            );

            $config = new StoredConfig(
                $request->getParsedBodyParam('config')
            );

            $classname = "\Leadvertex\External\Export\Format\\{$formatter}\\{$formatter}";
            /** @var FormatterInterface $formatter */
            $formatter = new $classname($apiParams, $this->runtimeDir, $this->outputDir);

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

            $formatter = $args['formatter'];
            $classname = "\Leadvertex\External\Export\Format\\{$formatter}\\{$formatter}";

            /** @var FormatterInterface $formatter */
            $formatter = new $classname(
                new ApiParams(
                    $request->getParsedBodyParam('api')['token'],
                    $request->getParsedBodyParam('api')['endpointUrl']
                ),
                $this->runtimeDir,
                $this->outputDir
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

            $tokensDir = Path::canonicalize("{$this->runtimeDir}/tokens");
            $handler = new DeferredRunner($tokensDir);
            $handler->prepend($formatter, $params);

            $command = "php {$this->consolePath} app:background {$batchToken}";

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