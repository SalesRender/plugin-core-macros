<?php
/**
 * Created for plugin-core-macros
 * Date: 02.12.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace SalesRender\Plugin\Core\Macros\Factories;


use Slim\App;

class WebAppFactory extends \SalesRender\Plugin\Core\Factories\WebAppFactory
{

    public function build(): App
    {
        $this
            ->addCors()
            ->addBatchActions()
            ->addAutocompleteAction()
            ->addTablePreviewAction();

        return parent::build();
    }

}