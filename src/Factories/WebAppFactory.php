<?php
/**
 * Created for plugin-core-macros
 * Date: 02.12.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Core\Macros\Factories;


use Slim\App;

class WebAppFactory extends \Leadvertex\Plugin\Core\Factories\WebAppFactory
{

    public function build(): App
    {
        $this
            ->addCors()
            ->addBatchActions()
            ->addAutocompleteAction();

        return parent::build();
    }

}