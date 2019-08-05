<?php
/**
 * Created for plugin-export-core
 * Datetime: 31.07.2019 18:25
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Exporter\Core\Controllers;


use Cocur\BackgroundProcess\BackgroundProcess;
use Leadvertex\Plugin\Components\Purpose\PluginClass;
use Leadvertex\Plugin\Components\Purpose\PluginPurpose;
use Leadvertex\Plugin\Components\Serializer\Serializer;
use Leadvertex\Plugin\Core\Exceptions\MismatchPurpose;
use Leadvertex\Plugin\Core\Helpers\ComponentFactory;
use Leadvertex\Plugin\Exporter\Core\Components\ExporterFactory;
use Leadvertex\Plugin\Exporter\Core\Components\GenerateParams;
use Slim\Http\Request;
use Slim\Http\Response;
use Webmozart\PathUtil\Path;
use XAKEPEHOK\EnumHelper\Exception\OutOfEnumException;

class ExporterController
{

    /** @var string */
    private $runtimeDir;

    /** @var string */
    private $consoleScript;

    /** @var bool */
    private $debugMode;

    public function __construct()
    {
        $this->runtimeDir = constant('LV_EXPORT_RUNTIME_DIR');
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
        $factory = new ComponentFactory($request->getParsedBody());
        $name = $args['exporter'];
        $exporter = ExporterFactory::create($name, $factory->getApiClient('api'));

        return $response->withJson(
            [
                'purpose' => [
                    'class' => PluginClass::CLASS_EXPORTER,
                    'entity' => $exporter->getEntity()->get(),
                ],
                'name' => $exporter::getName()->get(),
                'description' => $exporter::getDescription()->get(),
                'developer' => $exporter->getDeveloper()->toArray(),
                'languages' => [
                    'list' => $exporter::getLanguages(),
                    'default' => $exporter::getDefaultLanguage(),
                ],
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
        $factory = new ComponentFactory($request->getParsedBody());
        $name = $args['exporter'];
        $exporter = ExporterFactory::create($name, $factory->getApiClient('api'));

        $requestPurpose = $factory->getPurpose('purpose');
        $pluginPurpose = new PluginPurpose(
            new PluginClass(PluginClass::CLASS_EXPORTER),
            $exporter->getEntity()
        );

        if (!$pluginPurpose->isEquals($requestPurpose)) {
            return $response->withJson(['valid' => false],405);
        }

        $formData = $factory->getFormData('data');
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
        $factory = new ComponentFactory($request->getParsedBody());
        $name = $args['exporter'];
        $exporter = ExporterFactory::create($name, $factory->getApiClient('api'));

        $requestPurpose = $factory->getPurpose('purpose');
        $pluginPurpose = new PluginPurpose(
            new PluginClass(PluginClass::CLASS_EXPORTER),
            $exporter->getEntity()
        );

        if (!$pluginPurpose->isEquals($requestPurpose)) {
            throw new MismatchPurpose('Mismatch real plugin class & entity with data from request');
        }

        if ($this->debugMode) {

            $generateParams = new GenerateParams(
                $factory->getProcess('process'),
                $factory->getFormData('data'),
                $factory->getFsp('query')
            );

            $exporter->generate($generateParams);
            return $response->withJson(['result' => true],200);
        }

        $serializer = new Serializer(Path::canonicalize("{$this->runtimeDir}/serializer"));
        $uuid = $serializer->serialize([
            'name' => $name,
            'query' => $request->getParsedBody()
        ]);

        $command = "php {$this->consoleScript} app:background {$uuid}";
        $runner = new BackgroundProcess($command);
        $runner->run();

        return $response->withJson(['result' => true],200);
    }

}