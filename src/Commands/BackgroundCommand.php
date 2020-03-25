<?php
/**
 * Created for plugin-core.
 * Datetime: 03.07.2018 14:41
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Core\Macros\Commands;


use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Process\Components\Error;
use Leadvertex\Plugin\Components\Process\Process;
use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Core\Macros\Exceptions\SessionException;
use Leadvertex\Plugin\Core\Macros\MacrosPlugin;
use Leadvertex\Plugin\Core\Macros\Models\Session;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class BackgroundCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('app:background')
            ->setDescription('Run handle operation in background')
            ->addArgument('id', InputArgument::REQUIRED)
            ->addArgument('companyId', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws SessionException
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Connector::setCompanyId($input->getArgument('companyId'));
        $session = Session::findById($input->getArgument('id'));
        Session::start($session);

        $plugin = MacrosPlugin::getInstance();

        Translator::config($plugin::getDefaultLanguage());
        Translator::setLang(str_replace('-', '_', $session->lang));

        $plugin->setSession($session);
        $process = Process::findById($session->getId());

        try {
            $plugin->run($process, $session->fsp);
        } catch (Throwable $exception) {
            $error = new Error('Fatal plugin error. Please contact plugin developer: ' . $plugin::getDeveloper()->getEmail());
            $process->terminate($error);
            $process->save();
            throw $exception;
        }

        return 0;
    }

}