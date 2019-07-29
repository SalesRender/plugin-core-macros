<?php
/**
 * Created for plugin-export-core
 * Datetime: 25.06.2019 10:37
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Exporter\Core\Exceptions;


use Exception;
use Leadvertex\Plugin\Exporter\Core\Components\Entity;

class MismatchEntityException extends Exception
{

    public function __construct(Entity $supportedEntity, Entity $passedEntity)
    {
        $message = "This exporter can export only {$supportedEntity->get()}, but '{$passedEntity->get()}' was passed";
        parent::__construct($message);
    }

}