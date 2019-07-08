<?php
/**
 * Created for plugin-export-core
 * Datetime: 25.06.2019 11:15
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\Components;


use GuzzleHttp\Client;
use Leadvertex\Plugin\Export\Core\Components\BatchResult\BatchResultInterface;

class WebhookManager
{

    /**
     * @var BatchParams
     */
    private $params;
    /**
     * @var Client
     */
    private $client;

    public function __construct(BatchParams $params)
    {
        $this->params = $params;
        $this->client = new Client();
    }

    public function progress(array $ids)
    {
        $this->client->request(
            'POST',
            $url = $this->params->getProgressWebhookUrl(),
            ['form_params' => [
                'token' => $this->params->getToken(),
                'ids' => $ids
            ]]
        );
    }

    public function result(BatchResultInterface $batchResult)
    {
        $this->client->request(
            'POST',
            $url = $this->params->getResultWebhookUrl(),
            ['form_params' => [
                'token' => $this->params->getToken(),
                'data' => $batchResult->get()
            ]]
        );
    }

}