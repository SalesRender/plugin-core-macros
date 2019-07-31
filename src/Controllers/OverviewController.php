<?php
/**
 * Created for plugin-export-core
 * Datetime: 31.07.2019 18:16
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Exporter\Core\Controllers;


use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Leadvertex\Plugin\Exporter\Core\ExporterInterface;
use Slim\Http\Request;
use Slim\Http\Response;

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
        /** @var ExporterInterface[] $classes */
        $classes = ClassFinder::getClassesInNamespace('Leadvertex\Plugin\Exporter\Handler', ClassFinder::RECURSIVE_MODE);

        $data = [];
        foreach ($classes as $classname) {
            if (!is_a($classname, ExporterInterface::class, true)) {
                continue;
            }

            $name = substr(strrchr($classname, "\\"), 1);
            $data[$name] = [
                'name' => $classname::getName()->get(),
                'description' => $classname::getDescription()->get(),
            ];
        }

        return $response->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function exporter(Request $request, Response $response, $args)
    {
        $format = $args['exporter'];

        /** @var ExporterInterface $classname */
        $classname = "\Leadvertex\Plugin\Exporter\Handler\\{$format}\\{$format}";

        return $response->withJson([
            'name' => $classname::getName()->get(),
            'description' => $classname::getDescription()->get(),
        ]);
    }

}