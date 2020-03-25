<?php
/**
 * Created for plugin-core
 * Datetime: 31.07.2019 18:25
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Core\Macros\Controllers;


use Lcobucci\JWT\Parser;
use Leadvertex\Plugin\Components\ApiClient\ApiFilterSortPaginate;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Components\Form\FormData;
use Leadvertex\Plugin\Components\Handshake\Registration;
use Leadvertex\Plugin\Components\Process\Process;
use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Core\Macros\Components\InputToken;
use Leadvertex\Plugin\Core\Macros\Helpers\PathHelper;
use Leadvertex\Plugin\Core\Macros\Models\Session;
use Leadvertex\Plugin\Core\Macros\MacrosPlugin;
use Ramsey\Uuid\Uuid;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;
use XAKEPEHOK\Path\Path;

class PluginController
{

    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        $plugin = MacrosPlugin::getInstance();

        Translator::config($plugin::getDefaultLanguage());
        $lang = $request->getHeader('Accept-Language')[0] ?? Translator::getDefaultLang();
        Translator::setLang(str_replace('-', '_',$lang));

        if ($session = $this->loadSession()) {
            $plugin->setSession($session);
        }
    }

    public function info(): Response
    {
        return $this->response->withJson($this->getInfo());
    }

    public function registration(): Response
    {
        $parser = new Parser();
        $token = $parser->parse($this->request->getParsedBodyParam('registration'));

        Connector::setCompanyId($token->getClaim('companyId'));

        $registration = new Registration($token);
        $registration->save();

        return $this->response;
    }

    public function upload(): Response
    {
        $registration = Session::current()->getRegistration();

        /** @var UploadedFile $file */
        $file = $this->request->getUploadedFiles()['file'] ?? null;

        if (!$file) {
            return $this->response->withStatus(400);
        }

        $ext = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        if (empty($ext)) {
            return $this->response->withStatus(403);
        }

        $relative = (new Path('/'))
            ->down($registration->getCompanyId())
            ->down($registration->getFeature())
            ->down($registration->getId())
            ->down(Uuid::uuid4()->toString() . '.' . $ext);

        $pathOnDisk = PathHelper::getPublicUpload()->down($relative);

        $directory = $pathOnDisk->up();
        if (!is_dir((string) $directory)) {
            mkdir((string) $directory, 0755, true);
        }

        $file->moveTo((string) $pathOnDisk);


        $uriPath = (new Path($_ENV['LV_PLUGIN_SELF_URI']))->down('uploaded')->down($relative);
        return $this->response->withJson([
            'uri' => (string) $uriPath,
        ]);
    }

    public function autocomplete(string $name): Response
    {
        $autocomplete = MacrosPlugin::getInstance()->autocomplete($name);

        if (is_null($autocomplete)) {
            return $this->response->withStatus(404);
        }

        $query = $this->request->getQueryParam('query');

        if (is_array($query)) {
            return $this->response->withJson(
                $autocomplete->values($query)
            );
        }

        return $this->response->withJson(
            $autocomplete->query($query)
        );
    }

    public function getSettingsForm(): Response
    {
        return $this->getFormResponse(
            MacrosPlugin::getInstance()->getSettingsForm()
        );
    }

    public function getSettings(): Response
    {
        $form = MacrosPlugin::getInstance()->getSettingsForm();

        if (is_null($form)) {
            return $this->response->withStatus(404);
        }

        return $this->response->withJson($form->getData());
    }

    public function setSettings(): Response
    {
        $form = MacrosPlugin::getInstance()->getSettingsForm();
        $data = new FormData($this->request->getParsedBody());

        $response = $this->setFormData($form, $data);
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $settings = Session::current()->getSettings();
        $settings->setData($data);
        $settings->save();

        return $response;
    }

    public function getRunForm(int $number): Response
    {
        return $this->getFormResponse($this->getRunFormObject($number));
    }

    public function setRunOptions(int $number): Response
    {
        $form = $this->getRunFormObject($number);
        if (is_null($form)) {
            return $this->getFormResponse($form);
        }

        $data = new FormData($this->request->getParsedBody());

        $response = $this->setFormData($form, $data);
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $session = Session::current();

        $session->setOptions($number, $data);

        $session->save();

        return $response;
    }

    public function run(): Response
    {
        $session = Session::current();
        $session->save();

        $process = new Process($session->getId());

        if ((int) $_ENV['LV_PLUGIN_DEBUG']) {
            $process->setState(Process::STATE_PROCESSING);
            $process->save();

            $plugin = MacrosPlugin::getInstance();
            $plugin->run($process, $session->fsp);
        } else {
            $process->save();
        }

        return $this->response;
    }

    public function process(): Response
    {
        Connector::setCompanyId($this->request->getQueryParam('companyId'));
        $process = Process::findById($this->request->getQueryParam('id'));

        if (is_null($process)) {
            return $this->response->withStatus(404);
        }

        $session = Session::findById($this->request->getQueryParam('id'));

        return $this->response->withJson([
            'plugin' => [
                'id' => $session->getToken()->getRegistration()->getId(),
                'info' => $this->getInfo(),
            ],
            'process' => $process,
        ]);
    }

    private function getFormResponse(?Form $form): Response
    {
        if (is_null($form)) {
            return $this->response->withStatus(404);
        }
        return $this->response->withJson($form);
    }

    private function getRunFormObject(int $number): ?Form
    {
        $plugin = MacrosPlugin::getInstance();
        return $plugin->getRunForm($number);
    }

    private function setFormData(Form $form, FormData $data): Response
    {
        if (is_null($form)) {
            return $this->response->withStatus(404);
        }

        $form->setData($data);
        $errors = $form->getErrors($data);
        if (!empty($errors)) {
            return $this->response->withJson($errors, 400);
        }

        return $this->response;
    }


    private function getInfo(): array
    {
        $plugin = MacrosPlugin::getInstance();
        return [
            'name' => $plugin::getName(),
            'description' => $plugin::getDescription(),
            'purpose' => [
                'class' => $plugin::getPurpose()->getClass()->get(),
                'entity' => $plugin::getPurpose()->getEntity()->get(),
            ],
            'languages' => [
                'current' => Translator::getLang(),
                'default' => Translator::getDefaultLang(),
                'available' => Translator::getLanguages(),
            ],
            'developer' => $plugin::getDeveloper()->toArray(),
        ];
    }

    private function loadSession(): ?Session
    {
        $token = $this->request->getHeader('X-PLUGIN-TOKEN')[0] ?? '';

        if (empty($token)) {
            return null;
        }

        $companyId = (new Parser())->parse($token)->getClaim('cid');
        Connector::setCompanyId($companyId);

        $token = new InputToken($token);

        $session = Session::findById($token->getInputToken()->getClaim('jti'));
        if (is_null($session)) {

            $session = new Session(
                $token,
                new ApiFilterSortPaginate(
                    $this->request->getQueryParam('filters'),
                    $this->request->getQueryParam('sort'),
                    200
                ),
                Translator::getLang()
            );
        }

        Session::start($session);
        return $session;
    }
}