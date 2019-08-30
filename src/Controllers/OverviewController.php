<?php
/**
 * Created for plugin-core
 * Datetime: 31.07.2019 18:16
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Handler\Controllers;


use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Leadvertex\Plugin\Handler\PluginInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class OverviewController
{

    //TODO prettify output

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, Response $response)
    {
        /** @var PluginInterface[] $classes */
        $classes = ClassFinder::getClassesInNamespace('Leadvertex\Plugin\Handler', ClassFinder::RECURSIVE_MODE);

        $data = [];
        foreach ($classes as $classname) {
            if (!is_a($classname, PluginInterface::class, true)) {
                continue;
            }

            $name = substr(strrchr($classname, "\\"), 1);
            $data[$name] = [
                'name' => $classname::getName()->get(),
                'description' => $classname::getDescription()->get(),
            ];
        }

        return $this->asJson($response, $data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function handler(Request $request, Response $response, $args)
    {
        $format = $args['handler'];

        /** @var PluginInterface $classname */
        $classname = "\Leadvertex\Plugin\Handler\\{$format}\\{$format}";

        return $this->asJson($response, [
            'name' => $classname::getName()->get(),
            'description' => $classname::getDescription()->get(),
        ]);
    }

    private function asJson(Response $response, array $data, int $code = 200): Response
    {
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($code);
    }

}