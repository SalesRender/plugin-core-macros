<?php
namespace Leadvertex\Plugin\Exporter\Core\Apps;


use Leadvertex\Plugin\Core\Commands\CleanUpCommand;
use Leadvertex\Plugin\Exporter\Core\Commands\BackgroundCommand;
use Symfony\Component\Console\Application;

class ConsoleApplication extends Application
{

    use ConfigTrait;

    public function __construct()
    {
        $this->checkConfig();
        parent::__construct();

        $runtimeDir = constant('LV_EXPORT_RUNTIME_DIR');
        $outputDir = constant('LV_EXPORT_PUBLIC_DIR');

        $this->add(new CleanUpCommand([$runtimeDir, $outputDir]));
        $this->add(new BackgroundCommand($runtimeDir, $outputDir));
    }

}