<?php
namespace Leadvertex\External\Export\Core\Apps;


use Leadvertex\External\Export\Core\Commands\BackgroundCommand;
use Leadvertex\External\Export\Core\Commands\CleanUpCommand;
use Symfony\Component\Console\Application;

class ConsoleApplication extends Application
{

    /**
     * @var string
     */
    private $runtimeDir;
    /**
     * @var string
     */
    private $outputDir;

    public function __construct(string $runtimeDir, string $outputDir)
    {
        parent::__construct();

        $this->runtimeDir = $runtimeDir;
        $this->outputDir = $outputDir;

        $this->add(new CleanUpCommand($runtimeDir, $outputDir));
        $this->add(new BackgroundCommand($runtimeDir, $outputDir));
    }

}