<?php


namespace Leadvertex\Plugin\Exporter\Core\Apps;


use Leadvertex\Plugin\Exporter\Core\Controllers\ExporterController;
use Leadvertex\Plugin\Exporter\Core\Controllers\OverviewController;
use Slim\App;

class WebApplication extends App
{
    use ConfigTrait;

    public function __construct()
    {
        $this->checkConfig();
        parent::__construct([
            'settings' => [
                'displayErrorDetails' => constant('LV_EXPORT_DEBUG'),
                'addContentLengthHeader' => true,
            ],
        ]);

        $this->get('/', OverviewController::class . ':index');
        $this->get('/{exporter:[a-zA-Z][a-zA-Z\d_]*}', OverviewController::class . ':exporter');

        $pattern = '/{exporter:[a-zA-Z][a-zA-Z\d_]*}';
        $this->map(['LOAD'], $pattern, ExporterController::class . ':load');
        $this->map(['CHECK'], $pattern, ExporterController::class . ':check');
        $this->map(['EXPORT'], $pattern, ExporterController::class . ':export');
    }
}