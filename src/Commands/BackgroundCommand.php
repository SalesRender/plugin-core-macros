<?php
/**
 * Created for plugin-export-core.
 * Datetime: 03.07.2018 14:41
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Exporter\Core\Commands;


use Leadvertex\Plugin\Components\Serializer\Exceptions\InvalidUuidException;
use Leadvertex\Plugin\Components\Serializer\Exceptions\NotFoundUuidException;
use Leadvertex\Plugin\Components\Serializer\Serializer;
use Leadvertex\Plugin\Exporter\Core\Components\GenerateParams;
use Leadvertex\Plugin\Exporter\Core\ExporterInterface;
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
            ->addArgument('uuid', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws InvalidUuidException
     * @throws NotFoundUuidException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tokensDir = Path::canonicalize("{$this->runtimeDir}/serializer");
        $serializer = new Serializer($tokensDir);
        $data = $serializer->unserialize($input->getArgument('uuid'));

        /** @var ExporterInterface $exporter */
        $exporter = $data['exporter'];

        /** @var GenerateParams $generateParams */
        $generateParams = $data['generateParams'];

        $exporter->generate($generateParams);
    }

}