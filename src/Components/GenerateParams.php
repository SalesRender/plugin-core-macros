<?php
/**
 * @author Timur Kasumov (aka XAKEPEHOK)
 * Datetime: 20.06.2019 17:47
 */

namespace Leadvertex\Plugin\Exporter\Core\Components;


use Leadvertex\Plugin\Components\ApiClient\ApiFilterSortPaginate;
use Leadvertex\Plugin\Components\Form\FormData;
use Leadvertex\Plugin\Components\Process\Process;

class GenerateParams
{
    /**
     * @var Entity
     */
    private $entity;
    /**
     * @var FormData
     */
    private $formData;
    /**
     * @var Process
     */
    private $process;
    /**
     * @var ApiFilterSortPaginate
     */
    private $fsp;

    public function __construct(
        Process $process,
        FormData $formData,
        Entity $entity,
        ApiFilterSortPaginate $fsp
    )
    {
        $this->entity = $entity;
        $this->formData = $formData;
        $this->process = $process;
        $this->fsp = $fsp;
    }

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @return FormData
     */
    public function getFormData(): FormData
    {
        return $this->formData;
    }

    /**
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->process;
    }

    /**
     * @return ApiFilterSortPaginate
     */
    public function getFsp(): ApiFilterSortPaginate
    {
        return $this->fsp;
    }


}