<?php
/**
 * Created for plugin-core
 * Date: 20.03.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Core\Macros\Commands;



use Leadvertex\Plugin\Components\Db\Components\Connector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbCleanerCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('app:clean-db')
            ->setDescription('Remove outdated records from DB')
            ->addArgument('table', InputArgument::REQUIRED, 'Table name')
            ->addArgument('by', InputArgument::OPTIONAL, "Possible values: 'createdAt', 'updatedAt'", 'createdAt')
            ->addArgument('hours', InputArgument::OPTIONAL, 'Timeout in hours', 24)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = Connector::db();

        $table = $input->getArgument('table');
        $by = $input->getArgument('by');
        $hours = (int) $input->getArgument('hours');

        $data = $db->delete($table, [
            "AND" => [
                "{$by}[<]" => time() - ($hours * 60 * 60)
            ]
        ]);

        $output->writeln("Deleted {$data->rowCount()} records from table '{$table}'");

        return 0;
    }

}