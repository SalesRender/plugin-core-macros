<?php
/**
 * Created for plugin-core.
 * Datetime: 03.07.2018 14:41
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Handler\Commands;


use Leadvertex\Plugin\Components\Serializer\Exceptions\InvalidUuidException;
use Leadvertex\Plugin\Components\Serializer\Exceptions\NotFoundUuidException;
use Leadvertex\Plugin\Components\Serializer\Serializer;
use Leadvertex\Plugin\Handler\Factories\ComponentFactory;
use Leadvertex\Plugin\Handler\Components\PluginFactory;
use Leadvertex\Plugin\Handler\Components\HandleParams;
use Leadvertex\Plugin\Handler\PluginInterface;
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
            ->setDescription('Run handle operation in background')
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
        $serializerDir = Path::canonicalize("{$this->runtimeDir}/serializer");
        $serializer = new Serializer($serializerDir);
        $data = $serializer->unserialize($input->getArgument('uuid'));

        $name = $data['name'];
        $factory = new ComponentFactory($data['query']);

        /** @var PluginInterface $plugin */
        $plugin = PluginFactory::create($name, $factory->getApiClient('api'));

        $handleParams = new HandleParams(
            $factory->getProcess('process'),
            $factory->getFormData('settings'),
            $factory->getFormData('options'),
            $factory->getFsp('query')
        );

        $plugin->handle($handleParams);
    }

}