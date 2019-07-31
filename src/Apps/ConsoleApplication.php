<?php
namespace Leadvertex\Plugin\Exporter\Core\Apps;


use Leadvertex\Plugin\Core\Commands\CleanUpCommand;
use Leadvertex\Plugin\Exporter\Core\Commands\BackgroundCommand;
use Symfony\Component\Console\Application;

class ConsoleApplication extends Application
{

    use EnvironmentTrait;

    public function __construct(string $appDir)
    {
        parent::__construct();
        $this->loadEnvironment($appDir);

        $runtimeDir = getenv('LV_EXPORT_RUNTIME_DIR');
        $outputDir = getenv('LV_EXPORT_PUBLIC_DIR');

        $this->add(new CleanUpCommand([$runtimeDir, $outputDir]));
        $this->add(new BackgroundCommand($runtimeDir, $outputDir));
    }

}