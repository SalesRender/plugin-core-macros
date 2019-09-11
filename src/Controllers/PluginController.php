<?php
/**
 * Created for plugin-core
 * Datetime: 31.07.2019 18:25
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Handler\Controllers;


use Cocur\BackgroundProcess\BackgroundProcess;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Components\Form\FormData;
use Leadvertex\Plugin\Components\Serializer\Serializer;
use Leadvertex\Plugin\Handler\Exceptions\InvalidFormDataException;
use Leadvertex\Plugin\Handler\Exceptions\MismatchPurpose;
use Leadvertex\Plugin\Handler\Factories\ComponentFactory;
use Leadvertex\Plugin\Handler\Factories\PluginFactory;
use Leadvertex\Plugin\Handler\PluginInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Webmozart\PathUtil\Path;

class PluginController
{

    /**
     * @var string
     */
    private $pluginDir;
    /**
     * @var string
     */
    private $runtimeDir;
    /**
     * @var string
     */
    private $outputDir;
    /**
     * @var bool
     */
    private $debugMode;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;
    /**
     * @var array
     */
    private $args;
    /**
     * @var ComponentFactory
     */
    private $factory;
    /**
     * @var string
     */
    private $pluginName;
    /**
     * @var PluginInterface
     */
    private $plugin;

    public function __construct(Request $request, Response $response, array $args)
    {
        $this->pluginDir = constant('LV_PLUGIN_DIR');
        $this->runtimeDir = constant('LV_PLUGIN_DIR_RUNTIME');
        $this->outputDir = constant('LV_PLUGIN_DIR_OUTPUT');
        $this->debugMode = constant('LV_PLUGIN_DEBUG');

        $this->request = $request;
        $this->args = $args;

        $this->factory = new ComponentFactory($this->request->getParsedBody());
        $this->pluginName = $args['plugin'];
        $this->plugin = PluginFactory::create($this->pluginName, $this->factory->getApiClient('api'));

        if ($this->plugin->hasSettingsForm()) {
            $this->safeSetData(
                $this->plugin->getSettingsForm(),
                $this->factory->getFormData('settings'),
                new InvalidFormDataException('Invalid settings data')
            );
        }

        if ($this->plugin->hasOptionsForm()) {
            $this->safeSetData(
                $this->plugin->getOptionsForm(),
                $this->factory->getFormData('options'),
                new InvalidFormDataException('Invalid options data')
            );
        }

        $this->response = $response
            ->withHeader('X-Purpose-Class', $this->plugin->getPurpose()->getClass()->get())
            ->withHeader('X-Purpose-Entity', $this->plugin->getPurpose()->getEntity()->get());
    }

    /**
     * @return Response
     */
    public function load()
    {
        $plugin = $this->plugin;
        return $this->response->withJson([
            'purpose' => [
                'class' => $plugin->getPurpose()->getClass()->get(),
                'entity' => $plugin->getPurpose()->getEntity()->get(),
            ],
            'name' => $plugin::getName()->get(),
            'description' => $plugin::getDescription()->get(),
            'developer' => $plugin->getDeveloper()->toArray(),
            'languages' => [
                'list' => $plugin::getLanguages(),
                'default' => $plugin::getDefaultLanguage(),
            ],
            'forms' => [
                'settings' => $plugin->hasSettingsForm() ? $plugin->getSettingsForm()->toArray() : null,
                'options' => $plugin->hasOptionsForm() ? $plugin->getOptionsForm()->toArray() : null,
            ]
        ]);
    }

    /**
     * @return Response
     * @throws MismatchPurpose
     */
    public function handle()
    {
        $this->guardPurpose();

        $factory = $this->factory;
        $plugin = $this->plugin;

        if ($this->debugMode) {
            $plugin->handle(
                $factory->getProcess('process'),
                $factory->getFsp('query')
            );
            return $this->response->withJson(['result' => true], 200);
        }

        $serializer = new Serializer(Path::canonicalize("{$this->runtimeDir}/serializer"));
        $uuid = $serializer->serialize([
            'name' => $this->pluginName,
            'query' => $this->request->getParsedBody(),
        ]);

        $consoleScript = Path::canonicalize($this->pluginDir . '/console.php');
        $command = "php {$consoleScript} app:background {$uuid}";
        $runner = new BackgroundProcess($command);
        $runner->run();

        return $this->response->withJson(['result' => true]);
    }

    private function safeSetData(Form $form, ?FormData $formData, InvalidFormDataException $exception)
    {
        if ($formData) {
            if (!$form->validateData($formData)) {
                throw $exception;
            }
            $form->setData($formData);
        }
    }

    private function guardPurpose()
    {
        $requestPurpose = $this->factory->getPurpose('purpose');
        if (!$this->plugin->getPurpose()->isEquals($requestPurpose)) {
            throw new MismatchPurpose('Mismatch real plugin class & entity with data from request', 405);
        }
    }

}