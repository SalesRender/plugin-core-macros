<?php
/**
 * @author Timur Kasumov (aka XAKEPEHOK)
 * Datetime: 21.06.2019 22:22
 */

namespace Leadvertex\Plugin\Export\Core\Components;


use Exception;
use Leadvertex\Plugin\Export\Core\Components\BatchResult\BatchResultFailed;
use Leadvertex\Plugin\Export\Core\Exceptions\MismatchTypeException;
use Leadvertex\Plugin\Export\Core\Formatter\FormatterInterface;
use Webmozart\PathUtil\Path;

class DeferredRunner
{

    /**
     * @var string
     */
    private $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    public function prepend(FormatterInterface $formatter, GenerateParams $params)
    {
        $token = $params->getBatchParams()->getToken();
        $data = [
            'formatter' => base64_encode(serialize($formatter)),
            'params' => base64_encode(serialize($params)),
        ];
        file_put_contents($this->getFilePath($token), json_encode($data));
    }

    public function run(string $token)
    {
        $filePath = $this->getFilePath($token);
        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        /** @var FormatterInterface $formatter */
        $formatter = unserialize(base64_decode($data['formatter']));

        /** @var GenerateParams $params */
        $params = unserialize(base64_decode($data['params']));

        try {
            $this->guardType($formatter, $params);
            $formatter->generate($params);
        } catch (Exception $exception) {
            $manager = new WebhookManager($params->getBatchParams());
            $manager->result(new BatchResultFailed($exception->getMessage()));
            throw $exception;
        } finally {
            unlink($filePath);
        }
    }

    private function getFilePath(string $token)
    {
        $dir = Path::canonicalize($this->directory . '/' . substr($token, 0, 2));
        if (!is_dir($dir)) {
            mkdir($dir, 0666, true);
        }
        $path = Path::canonicalize("{$dir}/{$token}.json");
        return $path;
    }

    /**
     * @param FormatterInterface $formatter
     * @param GenerateParams $params
     * @throws MismatchTypeException
     */
    private function guardType(FormatterInterface $formatter, GenerateParams $params)
    {
        if (!$formatter->getScheme()->getType()->isEquals($params->getType())) {
            throw new MismatchTypeException('');
        }
    }

}