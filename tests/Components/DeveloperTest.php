<?php

namespace Leadvertex\Plugin\Export\Core\Components;


use PHPUnit\Framework\TestCase;

class DeveloperTest extends TestCase
{
    /** @var string */
    private $devName;
    /** @var string */
    private $email;
    /** @var string */
    private $hostname;
    /** @var string */
    private $sign;
    /** @var Developer */
    private $developer;

    public function setUp()
    {
        parent::setUp();

        $this->devName = 'testName';
        $this->email = 'test@mail.ru';
        $this->hostname = 'hostnameTest';
        $this->sign = 'signTest';

        $this->developer = new Developer(
            $this->devName,
            $this->email,
            $this->hostname,
            $this->sign
        );

    }

    public function testGetSign()
    {
        $this->assertEquals($this->sign, $this->developer->getSign());
    }

    public function testGetName()
    {
        $this->assertEquals($this->devName, $this->developer->getName());
    }

    public function testGetEmail()
    {
        $this->assertEquals($this->email, $this->developer->getEmail());
    }

    public function testGetHostname()
    {
        $this->assertEquals($this->hostname, $this->developer->getHostname());
    }

    public function testToArray()
    {
        $array = [
            'name' => $this->devName,
            'email' => $this->email,
            'hostname' => $this->hostname,
            'sign' => $this->sign
        ];

        $this->assertEquals($array, $this->developer->toArray());
    }
}
