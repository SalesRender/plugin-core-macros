<?php

namespace Leadvertex\Plugin\Export\Core\Components;


use PHPUnit\Framework\TestCase;

class ApiParamsTest extends TestCase
{

    /** @var string */
    private $token;
    /** @var ApiParams */
    private $apiParams;
    /** @var string */
    private $endPointUrl;

    public function setUp()
    {
        parent::setUp();

        $this->token = md5(random_bytes(256));
        $this->endPointUrl = 'https://example/index.php';

        $this->apiParams = new ApiParams($this->token, $this->endPointUrl);
    }

    public function testGetEndpointUrl()
    {
        $this->assertEquals($this->endPointUrl, $this->apiParams->getEndpointUrl());
    }

    public function testGetToken()
    {
        $this->assertEquals($this->token, $this->apiParams->getToken());
    }
}
