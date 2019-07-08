<?php
/**
 * Created for lv-export-core.
 * Datetime: 03.07.2018 14:41
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\Commands;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanUpCommand extends Command
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
            ->setName('app:clean-up')
            ->setDescription('Remove temporary and compiled files, older than 24 hours (or another timeout)')
            ->addArgument('hours', InputArgument::OPTIONAL, 'Timeout in hours', 24)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cleanUp(
            $this->runtimeDir,
            $input->getArgument('hours'),
            ['.gitignore'],
            $output
        );

        $this->cleanUp(
            $this->outputDir,
            $input->getArgument('hours'),
            ['.gitignore'],
            $output
        );
    }

    private function cleanUp(string $directory, int $hoursTimeout, array $exclude = [], OutputInterface $output)
    {
        $directory = realpath($directory);
        $directoryIterator = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);

        /** @var \RecursiveDirectoryIterator[] $iterator */
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) {

            if (in_array($item->getBasename(), $exclude)) {
                $output->writeln('Skip [exclude]: ' . $item->getRealPath());
                continue;
            }

            $deleteOlderThan = time() - 60*60*$hoursTimeout;
            $realpath = $item->getRealPath();

            if ($item->getMTime() < $deleteOlderThan) {
                if ($item->isDir()) {
                    $result = rmdir($realpath) ? 'Success' : 'Failed';
                    $output->writeln("Remove [directory]: {$realpath} [{$result}]");
                } else {
                    $result = unlink($realpath) ? 'Success' : 'Failed';
                    $output->writeln("Remove [file]: {$realpath} [{$result}]");
                }
            } else {
                $output->writeln('Skip [by timeout]: ' . $realpath);
            }
        }
    }

}