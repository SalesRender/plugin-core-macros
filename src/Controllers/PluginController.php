<?php
/**
 * Created for plugin-core
 * Datetime: 31.07.2019 18:25
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Handler\Controllers;


use Cocur\BackgroundProcess\BackgroundProcess;
use Leadvertex\Plugin\Components\Serializer\Serializer;
use Leadvertex\Plugin\Handler\Exceptions\MismatchPurpose;
use Leadvertex\Plugin\Handler\Factories\ComponentFactory;
use Leadvertex\Plugin\Handler\Factories\PluginFactory;
use Leadvertex\Plugin\Handler\PluginInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Webmozart\PathUtil\Path;
use XAKEPEHOK\EnumHelper\Exception\OutOfEnumException;

class PluginController
{

    /**
     * @var string
     */
    private $runtimeDir;
    /**
     * @var string
     */
    private $consoleScript;
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
        $this->runtimeDir = constant('LV_PLUGIN_DIR_RUNTIME');
        $this->consoleScript = constant('LV_PLUGIN_CONSOLE_SCRIPT');
        $this->debugMode = constant('LV_PLUGIN_DEBUG');

        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        $this->factory = new ComponentFactory($request->getParsedBody());
        $this->pluginName = $args['plugin'];
        $this->plugin = PluginFactory::create($this->pluginName, $this->factory->getApiClient('api'));
    }

    /**
     * @return Response
     */
    public function loadSettingsForm()
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
            'settings' => $plugin->hasSettingsForm() ? $plugin->getSettingsForm()->toArray() : null,
        ]);
    }

    /**
     * @return Response
     * @throws MismatchPurpose
     * @throws OutOfEnumException
     */
    public function loadOptionsForm()
    {
        $plugin = $this->plugin;
        $this->guardPurpose();

        $options = null;
        if ($plugin->hasOptionsForm()) {
            $settingsFormData = $this->factory->getFormData('settings');
            $fsp = $this->factory->getFsp('query');
            $options = $plugin->getOptionsForm($settingsFormData, $fsp);
        }

        return $this->asJson([
            'options' => $options
        ]);
    }

    /**
     * @return Response
     * @throws OutOfEnumException
     */
    public function check()
    {
        $factory = $this->factory;
        $plugin = $this->plugin;

        try {
            $this->guardPurpose();
        } catch (MismatchPurpose $exception) {
            return $this->asJson([
                'valid' => false,
                'error' => $exception->getMessage(),
            ], 405);
        }

        $settingsData = $factory->getFormData('settings');
        if ($plugin->hasSettingsForm() && !$plugin->getSettingsForm()->validateData($settingsData)) {
            return $this->asJson([
                'valid' => false,
                'error' => 'Invalid settings form data',
            ], 400);
        }

        return $this->asJson(['valid' => true]);
    }

    /**
     * @return Response
     * @throws MismatchPurpose
     * @throws OutOfEnumException
     */
    public function handle()
    {
        $factory = $this->factory;
        $plugin = $this->plugin;

        $this->guardPurpose();

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

        $command = "php {$this->consoleScript} app:background {$uuid}";
        $runner = new BackgroundProcess($command);
        $runner->run();

        return $this->asJson(['result' => true], 200);
    }

    /**
     * @throws MismatchPurpose
     * @throws OutOfEnumException
     */
    private function guardPurpose()
    {
        $requestPurpose = $this->factory->getPurpose('purpose');
        if (!$this->plugin->getPurpose()->isEquals($requestPurpose)) {
            throw new MismatchPurpose('Mismatch real plugin class & entity with data from request');
        }
    }

    private function asJson(array $data, int $code = 200): Response
    {
        $payload = json_encode($data);
        $this->response->getBody()->write($payload);
        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($code);
    }

}