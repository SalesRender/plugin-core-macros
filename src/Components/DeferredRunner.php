<?php
/**
 * @author Timur Kasumov (aka XAKEPEHOK)
 * Datetime: 21.06.2019 22:22
 */

namespace Leadvertex\External\Export\Core\Components;


use Leadvertex\External\Export\Core\FormatterInterface;
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
            $formatter->generate($params);
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

}