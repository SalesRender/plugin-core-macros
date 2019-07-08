<?php

namespace Leadvertex\Plugin\Export\Core\Components;


use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MultiLangTest extends TestCase
{
    /** @var array */
    private $translations;
    /** @var MultiLang */
    private $multiLang;

    public function setUp()
    {
        parent::setUp();

        $this->translations = array('en' => 'Organization name', 'ru' => 'Название организации');

        $this->multiLang = new MultiLang($this->translations);
    }

    public function testCreateMultiLangWithInvalidCodeSize()
    {
        $this->expectException(InvalidArgumentException::class);
        new MultiLang(['NaN' => 'bodyText']);
    }

    public function testCreateMultiLangWithInvalidText()
    {
        $this->expectException(InvalidArgumentException::class);
        new MultiLang(['ru' => 55]);
    }

    public function testCreateMultiLangWithEmptyText()
    {
        $this->expectException(InvalidArgumentException::class);
        new MultiLang(['ru' => null]);
    }

    public function testToArray()
    {
        $expected = [$this->translations];

        $this->assertEquals($expected, MultiLang::toArray([$this->multiLang]));
    }

    public function testGetTranslations()
    {
        $this->assertEquals($this->translations, $this->multiLang->getTranslations());
    }

}
