<?php
/**
 * Created for plugin-export-core
 * Datetime: 31.07.2019 18:25
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Exporter\Core\Controllers;


use Cocur\BackgroundProcess\BackgroundProcess;
use Leadvertex\Plugin\Components\ApiClient\ApiClient;
use Leadvertex\Plugin\Components\Purpose\PluginClass;
use Leadvertex\Plugin\Components\Purpose\PluginPurpose;
use Leadvertex\Plugin\Components\Serializer\Serializer;
use Leadvertex\Plugin\Core\Exceptions\MismatchPurpose;
use Leadvertex\Plugin\Core\Helpers\PluginRequest;
use Leadvertex\Plugin\Exporter\Core\Components\GenerateParams;
use Leadvertex\Plugin\Exporter\Core\ExporterInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Webmozart\PathUtil\Path;
use XAKEPEHOK\EnumHelper\Exception\OutOfEnumException;

class ExporterController
{

    /** @var string */
    private $runtimeDir;

    /** @var string */
    private $publicDir;

    /** @var string */
    private $publicUrl;

    /** @var string */
    private $consoleScript;

    /** @var bool */
    private $debugMode;

    public function __construct()
    {
        $this->runtimeDir = constant('LV_EXPORT_RUNTIME_DIR');
        $this->publicDir = constant('LV_EXPORT_PUBLIC_DIR');
        $this->publicUrl = constant('LV_EXPORT_PUBLIC_URL');
        $this->consoleScript = constant('LV_EXPORT_CONSOLE_SCRIPT');
        $this->debugMode = constant('LV_EXPORT_DEBUG');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function load(Request $request, Response $response, $args)
    {
        $pluginRequest = new PluginRequest($request);
        $name = $args['exporter'];
        $exporter = $this->getExporter($name, $pluginRequest->getApiClient('api'));

        return $response->withJson(
            [
                'developer' => $exporter->getDeveloper()->toArray(),
                'purpose' => [
                    'class' => PluginClass::CLASS_EXPORTER,
                    'entity' => $exporter->getEntity()->get(),
                ],
                'languages' => $exporter::getLanguages(),
                'form' => $exporter->getForm()->toArray(),
            ],
            200
        );
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws OutOfEnumException
     */
    public function check(Request $request, Response $response, $args)
    {
        $pluginRequest = new PluginRequest($request);
        $name = $args['exporter'];
        $exporter = $this->getExporter($name, $pluginRequest->getApiClient('api'));

        $requestPurpose = $pluginRequest->getPurpose('purpose');
        $pluginPurpose = new PluginPurpose(
            new PluginClass(PluginClass::CLASS_EXPORTER),
            $exporter->getEntity()
        );

        if (!$pluginPurpose->isEquals($requestPurpose)) {
            return $response->withJson(['valid' => false],405);
        }

        $formData = $pluginRequest->getFormData('data');
        if (!$exporter->getForm()->validateData($formData)) {
            return $response->withJson(['valid' => false],400);
        }

        return $response->withJson(['valid' => true],200);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws MismatchPurpose
     * @throws OutOfEnumException
     */
    public function export(Request $request, Response $response, $args)
    {
        $pluginRequest = new PluginRequest($request);
        $name = $args['exporter'];
        $exporter = $this->getExporter($name, $pluginRequest->getApiClient('api'));

        $requestPurpose = $pluginRequest->getPurpose('purpose');
        $pluginPurpose = new PluginPurpose(
            new PluginClass(PluginClass::CLASS_EXPORTER),
            $exporter->getEntity()
        );

        if (!$pluginPurpose->isEquals($requestPurpose)) {
            throw new MismatchPurpose('Mismatch real plugin class & entity with data from request');
        }

        $generateParams = new GenerateParams(
            $pluginRequest->getProcess('process'),
            $pluginRequest->getFormData('data'),
            $pluginRequest->getFsp('query')
        );

        if ($this->debugMode) {
            $exporter->generate($generateParams);
            return $response->withJson(['result' => true],200);
        }

        $serializerDir = Path::canonicalize("{$this->runtimeDir}/serializer");
        $handler = new Serializer($serializerDir);
        $uuid = $handler->serialize([
            'exporter' => $exporter,
            'generateParams' => $generateParams
        ]);

        $command = "php {$this->consoleScript} app:background {$uuid}";
        $runner = new BackgroundProcess($command);
        $runner->run();

        return $response->withJson(['result' => true],200);
    }

    private function getExporter(string $name, ApiClient $client): ExporterInterface
    {
        $classname = "\Leadvertex\Plugin\Exporter\Handler\\{$name}\\{$name}";
        return new $classname(
            $client,
            $this->runtimeDir,
            $this->publicDir,
            $this->publicUrl
        );
    }

}