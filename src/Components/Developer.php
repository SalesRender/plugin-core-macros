<?php
/**
 * Created for plugin-export-core
 * Datetime: 25.06.2019 12:23
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\Components;


class Developer
{

    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $hostname;
    /**
     * @var string
     */
    private $sign;

    /**
     * Developer constructor.
     * @param string $name of company or developer
     * @param string $email of support this export
     * @param string $hostname hostname of this export (e.g. example.com)
     * @param string $sign string, provided by leadvertex.com for developer verification (not required)
     */
    public function __construct(string $name, string $email, string $hostname, string $sign = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->hostname = $hostname;
        $this->sign = $sign;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @return string
     */
    public function getSign(): string
    {
        return $this->sign;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'hostname' => $this->hostname,
            'sign' => $this->sign,
        ];
    }

}