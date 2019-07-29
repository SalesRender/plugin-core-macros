<?php


namespace Leadvertex\Plugin\Exporter\Core\Apps;


use Cocur\BackgroundProcess\BackgroundProcess;
use HaydenPierce\ClassFinder\ClassFinder;
use Leadvertex\Plugin\Components\ApiClient\ApiFilterSortPaginate;
use Leadvertex\Plugin\Components\Process\Process;
use Leadvertex\Plugin\Components\Serializer\Serializer;
use Leadvertex\Plugin\Exporter\Core\Components\GenerateParams;
use Leadvertex\Plugin\Exporter\Core\Components\Entity;
use Leadvertex\Plugin\Exporter\Core\Exceptions\MismatchEntityException;
use Leadvertex\Plugin\Exporter\Core\FormatterInterface;
use Leadvertex\Plugin\Exporter\Core\Helpers\RequestHelper;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Webmozart\PathUtil\Path;

/**
 * Class WebApplication
 * @package Leadvertex\Plugin\Exporter\Core\Apps
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
            $classes = ClassFinder::getClassesInNamespace('Leadvertex\Plugin\Exporter\Format', ClassFinder::RECURSIVE_MODE);

            $data = [];
            foreach ($classes as $classname) {
                if (!is_a($classname, FormatterInterface::class, true)) {
                    continue;
                }

                $name = substr(strrchr($classname, "\\"), 1);
                $data[$name] = [
                    'name' => $classname::getName()->get(),
                    'description' => $classname::getDescription()->get(),
                ];
            }

            return $response->withJson($data);
        });

        //TODO prettify output
        $this->get('/{formatter:[a-zA-Z][a-zA-Z\d_]*}', function (Request $request, Response $response, $args) {
            $format = $args['formatter'];

            /** @var FormatterInterface $classname */
            $classname = "\Leadvertex\Plugin\Exporter\Format\\{$format}\\{$format}";

            return $response->withJson([
                'name' => $classname::getName()->get(),
                'description' => $classname::getDescription()->get(),
            ]);
        });

        $this->rpc('CONFIG', function (Request $request, Response $response, $args) {
            $format = $args['formatter'];

            $apiClient = RequestHelper::getApiParams($request);
            $classname = "\Leadvertex\Plugin\Exporter\Format\\{$format}\\{$format}";
            /** @var FormatterInterface $formatter */
            $formatter = new $classname($apiClient, $this->runtimeDir, $this->publicDir, $this->publicUrl);
            return $response->withJson(
                [
                    'developer' => $formatter->getDeveloper()->toArray(),
                    'class' => 'EXPORTER',
                    'entity' => $formatter->getEntity()->get(),
                    'form' => $formatter->getForm()->toArray(),
                ],
                200
            );
        });

        $this->rpc('VALIDATE', function (Request $request, Response $response, $args) {
            $format = $args['formatter'];

            $apiClient = RequestHelper::getApiParams($request);
            $formData = RequestHelper::getFormDataConfig($request);

            $classname = "\Leadvertex\Plugin\Exporter\Format\\{$format}\\{$format}";
            /** @var FormatterInterface $formatter */
            $formatter = new $classname($apiClient, $this->runtimeDir, $this->publicDir, $this->publicUrl);

            $entity = new Entity($request->getParsedBodyParam('entity'));
            if (!$entity->isEquals($formatter->getEntity())) {
                return $response->withJson(['valid' => false],405);
            }

            if (!$formatter->getForm()->validateData($formData)) {
                return $response->withJson(['valid' => false],400);
            }

            return $response->withJson(['valid' => true],200);
        });

        $this->rpc('GENERATE', function (Request $request, Response $response, $args) {

            $format = $args['formatter'];
            $classname = "\Leadvertex\Plugin\Exporter\Format\\{$format}\\{$format}";

            /** @var FormatterInterface $formatter */
            $formatter = new $classname(
                RequestHelper::getApiParams($request),
                $this->runtimeDir,
                $this->publicDir,
                $this->publicUrl
            );

            $entity = new Entity($request->getParsedBodyParam('entity'));
            if (!$entity->isEquals($formatter->getEntity())) {
                throw new MismatchEntityException($formatter->getEntity(), $entity);
            }

            $processData = $request->getParsedBodyParam('process');
            $process = new Process(
                $processData['id'],
                $processData['initUrl'],
                $processData['successUrl'],
                $processData['errorUrl'],
                $processData['skipUrl'],
                $processData['resultUrl']
            );

            $fspQuery = $request->getParsedBodyParam('query');
            $fsp = new ApiFilterSortPaginate(
                $fspQuery['filter'] ?? null,
                $fspQuery['sort'] ?? null,
                $fspQuery['pageSize'] ?? null
            );

            $generateParams = new GenerateParams(
                $process,
                RequestHelper::getFormDataConfig($request),
                $entity,
                $fsp
            );

            if ($this->debugMode) {
                $formatter->generate($generateParams);
                return $response->withJson(['result' => true],200);
            }

            $serializerDir = Path::canonicalize("{$this->runtimeDir}/serializer");
            $handler = new Serializer($serializerDir);
            $uuid = $handler->serialize([
                'formatter' => $formatter,
                'generateParams' => $generateParams
            ]);

            $command = "php {$this->consoleScript} app:background {$uuid}";
            $runner = new BackgroundProcess($command);
            $runner->run();

            return $response->withJson(['result' => true],200);
        });
    }

    private function rpc(string $method, callable $callable)
    {
        $this->map([$method], '/{formatter:[a-zA-Z][a-zA-Z\d_]*}', $callable);
    }

}