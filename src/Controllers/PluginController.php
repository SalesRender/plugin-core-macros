<?php
/**
 * Created for plugin-core
 * Datetime: 31.07.2019 18:25
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Handler\Controllers;


use Cocur\BackgroundProcess\BackgroundProcess;
use Leadvertex\Plugin\Components\Serializer\Serializer;
use Leadvertex\Plugin\Handler\Exceptions\InvalidFormDataException;
use Leadvertex\Plugin\Handler\Exceptions\MismatchPurpose;
use Leadvertex\Plugin\Handler\Factories\ComponentFactory;
use Leadvertex\Plugin\Handler\Factories\PluginFactory;
use Leadvertex\Plugin\Handler\PluginInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
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

        $this->factory = new ComponentFactory($request->getParsedBody());
        $this->pluginName = $args['plugin'];
        $this->plugin = PluginFactory::create($this->pluginName, $this->factory->getApiClient('api'));

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
        return $this->asJson([
            'purpose' => [
                'class' => $plugin->getPurpose()->getClass(),
                'entity' => $plugin->getPurpose()->getEntity(),
            ],
            'name' => $plugin::getName()->get(),
            'description' => $plugin::getDescription()->get(),
            'developer' => $plugin->getDeveloper()->toArray(),
            'languages' => [
                'list' => $plugin::getLanguages(),
                'default' => $plugin::getDefaultLanguage(),
            ],
        ]);
    }

    public function loadSettingsForm()
    {
        $plugin = $this->plugin;
        return $this->asJson([
            'settings' => $plugin->hasSettingsForm() ? $plugin->getSettingsForm()->toArray() : null
        ]);
    }

    /**
     * @return Response
     * @throws MismatchPurpose
     */
    public function loadOptionsForm()
    {
        $this->guardPurpose();
        $plugin = $this->plugin;
        $factory = $this->factory;

        $options = null;
        if ($plugin->hasOptionsForm()) {

            if ($plugin->hasSettingsForm()) {
                $settingsFormData = $factory->getFormData('settings');
                $plugin->getSettingsForm()->setData($settingsFormData);
            }

            $options = $plugin->getOptionsForm($factory->getFsp('query'));
        }

        return $this->asJson([
            'options' => $options
        ]);
    }

    /**
     * @return Response
     */
    public function validateSettingsForm()
    {
        try {
            $this->guardSettingsData();
        } catch (InvalidFormDataException $exception) {
            return $this->asJson([
                'valid' => false,
                'error' => $exception->getMessage(),
            ], $exception->getCode());
        }

        return $this->asJson([
            'valid' => true,
            'error' => null
        ]);
    }

    /**
     * @return Response
     * @throws InvalidFormDataException
     * @throws MismatchPurpose
     */
    public function handle()
    {
        $this->guardPurpose();
        $this->guardSettingsData();
        $this->guardOptionsData();

        $factory = $this->factory;
        $plugin = $this->plugin;

        if ($this->debugMode) {
            $plugin->handle(
                $factory->getProcess('process'),
                $factory->getFormData('settings'),
                $factory->getFormData('options'),
                $factory->getFsp('query')
            );
            return $this->asJson(['result' => true], 200);
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

        return $this->asJson(['result' => true], 200);
    }

    private function asJson(array $data, int $code = 200): Response
    {
        $payload = json_encode($data);
        $this->response->getBody()->write($payload);
        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($code);
    }

    private function guardPurpose()
    {
        $requestPurpose = $this->factory->getPurpose('purpose');
        if (!$this->plugin->getPurpose()->isEquals($requestPurpose)) {
            throw new MismatchPurpose('Mismatch real plugin class & entity with data from request', 405);
        }
    }

    private function guardSettingsData()
    {
        $settingsData = $this->factory->getFormData('settings');
        if ($this->plugin->hasSettingsForm() && !$this->plugin->getSettingsForm()->validateData($settingsData)) {
            throw new InvalidFormDataException('Invalid settings data', 400);
        }
    }

    private function guardOptionsData()
    {
        $fsp = $this->factory->getFsp('query');
        $optionsData = $this->factory->getFormData('options');
        if ($this->plugin->hasOptionsForm() && !$this->plugin->getOptionsForm($fsp)->validateData($optionsData)) {
            throw new InvalidFormDataException('Invalid options data', 400);
        }
    }

}