<?php

namespace Leadvertex\External\Export\Core\Components;


use PHPUnit\Framework\TestCase;

class BatchParamsTest extends TestCase
{
    /** @var string */
    private $token;
    /** @var string */
    private $progressWebhookUrl;
    /** @var string */
    private $resultWebhookUrl;
    /** @var BatchParams */
    private $batchPartams;

    public function setUp()
    {
        parent::setUp();

        $this->token= md5(random_bytes(256));
        $this->progressWebhookUrl = 'https://progress-url.ru';
        $this->resultWebhookUrl = 'https://result-url.ru';

        $this->batchPartams = new BatchParams($this->token, $this->progressWebhookUrl, $this->resultWebhookUrl);
    }

    public function testGetToken()
    {
        $this->assertEquals($this->token, $this->batchPartams->getToken());
    }

    public function testGetResultWebhookUrl()
    {
        $this->assertEquals($this->resultWebhookUrl, $this->batchPartams->getResultWebhookUrl());
    }

    public function testGetProgressWebhookUrl()
    {
        $this->assertEquals($this->progressWebhookUrl, $this->batchPartams->getProgressWebhookUrl());
    }
}
