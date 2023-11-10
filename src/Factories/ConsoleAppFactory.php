<?php
/**
 * Created for plugin-core-macros
 * Date: 02.12.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace SalesRender\Plugin\Core\Macros\Factories;


use Symfony\Component\Console\Application;

class ConsoleAppFactory extends \SalesRender\Plugin\Core\Factories\ConsoleAppFactory
{

    public function build(): Application
    {
        $this->addBatchCommands();
        return parent::build();
    }

}