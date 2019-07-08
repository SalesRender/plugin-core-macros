<?php
/**
 * @author Timur Kasumov (aka XAKEPEHOK)
 * Datetime: 21.06.2019 22:02
 */

namespace Leadvertex\Plugin\Export\Core\Components;


class BatchParams
{

    /**
     * @var string
     */
    private $token;
    /**
     * @var string
     */
    private $progressWebhookUrl;
    /**
     * @var string
     */
    private $resultWebhookUrl;

    public function __construct(
        string $token,
        string $progressWebhookUrl,
        string $resultWebhookUrl
    )
    {
        $this->token = $token;
        $this->progressWebhookUrl = $progressWebhookUrl;
        $this->resultWebhookUrl = $resultWebhookUrl;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getProgressWebhookUrl(): string
    {
        return $this->progressWebhookUrl;
    }

    /**
     * @return string
     */
    public function getResultWebhookUrl(): string
    {
        return $this->resultWebhookUrl;
    }

}