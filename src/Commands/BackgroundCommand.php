<?php
/**
 * Created for lv-export-core.
 * Datetime: 03.07.2018 14:41
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\Commands;


use Leadvertex\Plugin\Export\Core\Components\DeferredRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\PathUtil\Path;

class BackgroundCommand extends Command
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
    }

    protected function configure()
    {
        $this
            ->setName('app:background')
            ->setDescription('Run generate operation in background')
            ->addArgument('token', InputArgument::REQUIRED, 'Batch token');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tokensDir = Path::canonicalize("{$this->runtimeDir}/tokens");
        $handler = new DeferredRunner($tokensDir);
        $handler->run($input->getArgument('token'));
    }

}