<?php
/**
 * Created for plugin-core
 * Datetime: 31.07.2019 18:25
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Core\Macros\Controllers;


use Lcobucci\JWT\Parser;
use Leadvertex\Plugin\Components\ApiClient\ApiFilterSortPaginate;
use Leadvertex\Plugin\Components\ApiClient\ApiSort;
use Leadvertex\Plugin\Components\Batch\Batch;
use Leadvertex\Plugin\Components\Batch\Exceptions\BatchException;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Components\Form\FormData;
use Leadvertex\Plugin\Components\Registration\Registration;
use Leadvertex\Plugin\Components\Process\Process;
use Leadvertex\Plugin\Components\Token\InputToken;
use Leadvertex\Plugin\Components\Token\InputTokenInterface;
use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Core\Macros\Helpers\PathHelper;
use Leadvertex\Plugin\Core\Macros\MacrosPlugin;
use Ramsey\Uuid\Uuid;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;
use Slim\Psr7\UploadedFile;
use XAKEPEHOK\Path\Path;

class PluginController
{

    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var InputTokenInterface */
    private $token;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        $lang = $request->getHeader('Accept-Language')[0] ?? Translator::getDefaultLang();
        Translator::setLang(str_replace('-', '_',$lang));

        $this->token = $this->loadToken();
    }

    public function info(): Response
    {
        return $this->response->withJson($this->getInfo());
    }

    public function registration(): Response
    {
        $parser = new Parser();
        $token = $parser->parse($this->request->getParsedBodyParam('registration'));

        Connector::setCompanyId($token->getClaim('cid'));

        $registration = new Registration($token);

        $old = Registration::findById($registration->getId(), $registration->getFeature());
        if ($old) {
            $old->delete();
        }

        $registration->save();

        return $this->response;
    }

    public function upload(): Response
    {
        $registration = $this->token->getRegistration();

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

    public function getSettingsData(): Response
    {
        $form = MacrosPlugin::getInstance()->getSettingsForm();

        if (is_null($form)) {
            return $this->response->withStatus(404);
        }

        return $this->response->withJson($this->token->getSettings());
    }

    public function setSettingsData(): Response
    {
        $form = MacrosPlugin::getInstance()->getSettingsForm();
        $data = new FormData($this->request->getParsedBody());

        $response = $this->setFormData($form, $data);
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $settings = $this->token->getSettings();
        $settings->setData($data);
        $settings->save();

        return $response;
    }

    public function batchPrepare(): Response
    {
        $batch = Batch::findById($this->token->getId());
        if ($batch) {
            return $this->response->withStatus(409);
        }

        $filters = json_decode($this->request->getQueryParam('filters', '[]'), true);
        $sort = json_decode($this->request->getQueryParam('sort'), true);
        if ($sort && isset($sort['field']) && isset($sort['direction'])) {
            $sort = new ApiSort($sort['field'], $sort['direction']);
        } else {
            $sort = null;
        }

        $batch = new Batch(
            $this->token,
            new ApiFilterSortPaginate($filters, $sort, 200),
            Translator::getLang()
        );
        $batch->save();

        return $this->response->withStatus(201);
    }

    public function getBatchForm(int $number): Response
    {
        return $this->getFormResponse($this->getBatchFormObject($number));
    }

    public function setBatchData(int $number): Response
    {
        $form = $this->getBatchFormObject($number);
        if (is_null($form)) {
            return $this->getFormResponse($form);
        }

        $data = new FormData($this->request->getParsedBody());

        $response = $this->setFormData($form, $data);
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $batch = $this->getBatch();
        $batch->setOptions($number, $data);
        $batch->save();

        return $response;
    }

    public function run(): Response
    {
        $process = new Process($this->token->getId());
        if ((int) $_ENV['LV_PLUGIN_DEBUG']) {
            $process->setState(Process::STATE_PROCESSING);
            $process->save();

            $plugin = MacrosPlugin::getInstance();
            $handler = $plugin->handler();
            $handler($process, $this->getBatch());
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

        $session = Batch::findById($this->request->getQueryParam('id'));

        return $this->response->withJson([
            'plugin' => [
                'id' => $session->getToken()->getRegistration()->getId(),
                'info' => $this->getInfo(),
            ],
            'process' => $process,
        ]);
    }

    /**
     * @return Batch
     * @throws BatchException
     */
    private function getBatch(): Batch
    {
        $batch = Batch::findById($this->token->getId());
        if (is_null($batch)) {
            throw new BatchException("Batch was not init");
        }
        return $batch;
    }

    private function getFormResponse(?Form $form): Response
    {
        if (is_null($form)) {
            return $this->response->withStatus(404);
        }
        return $this->response->withJson($form);
    }

    private function getBatchFormObject(int $number): ?Form
    {
        $plugin = MacrosPlugin::getInstance();
        return $plugin->getBatchForm($number);
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
            'type' => 'MACROS',
            'extra' => [
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

    private function loadToken(): ?InputTokenInterface
    {
        $jwt = $this->request->getHeader('X-PLUGIN-TOKEN')[0] ?? '';

        if (empty($jwt)) {
            return null;
        }

        $token = new InputToken($jwt);
        Connector::setCompanyId($token->getCompanyId());
        return $token;
    }
}