<?php
/**
 * Created for plugin-core
 * Datetime: 24.07.2019 12:24
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Handler\Factories;


use Adbar\Dot;
use Leadvertex\Plugin\Components\ApiClient\ApiClient;
use Leadvertex\Plugin\Components\ApiClient\ApiFilterSortPaginate;
use Leadvertex\Plugin\Components\ApiClient\ApiSort;
use Leadvertex\Plugin\Components\Form\FormData;
use Leadvertex\Plugin\Components\Process\Process;
use Leadvertex\Plugin\Components\Purpose\PluginClass;
use Leadvertex\Plugin\Components\Purpose\PluginEntity;
use Leadvertex\Plugin\Components\Purpose\PluginPurpose;
use XAKEPEHOK\EnumHelper\Exception\OutOfEnumException;

class ComponentFactory
{

    /**
     * @var array
     */
    private $data;

    private $body;

    /**
     * PluginRequest constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getBody(): Dot
    {
        if ($this->body === null) {
            $this->body = new Dot($this->data);
        }
        return $this->body;
    }

    public function getApiClient(string $key): ?ApiClient
    {
        $body = $this->getBody();
        if ($body->has("{$key}.token") && $body->has("{$key}.endpointUrl")) {
            return new ApiClient(
                $body->get("{$key}.endpointUrl"),
                $body->get("{$key}.token")
            );
        }
        return null;
    }

    public function getFormData(string $key): ?FormData
    {
        $body = $this->getBody();
        if ($body->has($key)) {
            return new FormData($body->get($key));
        }
        return null;
    }

    public function getFsp(string $key): ?ApiFilterSortPaginate
    {
        $body = $this->getBody();
        if ($body->has($key)) {

            $sort = null;
            $sortField = $body->get("{$key}.sort.field");
            $sortDirection = $body->get("{$key}.sort.direction");
            if ($sortField && $sortDirection) {
                $sort = new ApiSort($sortField, $sortDirection);
            }

            return new ApiFilterSortPaginate(
                $body->get("{$key}.filters"),
                $sort,
                $body->get("{$key}.pagination.pageSize")
            );
        }
        return null;
    }

    /**
     * @param string $key
     * @return PluginPurpose|null
     * @throws OutOfEnumException
     */
    public function getPurpose(string $key): ?PluginPurpose
    {
        $body = $this->getBody();
        if ($body->has("{$key}.class") && $body->has("{$key}.entity")) {
            return new PluginPurpose(
                new PluginClass($body->get("{$key}.class")),
                new PluginEntity($body->get("{$key}.entity"))
            );
        }
        return null;
    }

    public function getProcess(string $key): ?Process
    {
        $body = $this->getBody();
        if ($body->has("{$key}.id") && $body->has("{$key}.webhook")) {
            return new Process(
                $body->get("{$key}.id"),
                $body->get("{$key}.webhook.initUrl"),
                $body->get("{$key}.webhook.handleUrl"),
                $body->get("{$key}.webhook.errorUrl"),
                $body->get("{$key}.webhook.skipUrl"),
                $body->get("{$key}.webhook.resultUrl")
            );
        }
        return null;
    }

}