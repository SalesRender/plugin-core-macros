<?php
/**
 * Created for lv-export-core
 * Datetime: 28.06.2019 14:58
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\Components;


use Exception;
use InvalidArgumentException;

class MultiLang
{

    /**
     * @var array
     */
    private $translations;

    /**
     * MultiLang constructor.
     * @param array $translations. For example array('en' => 'Organization name', 'ru' => 'Название организации').
     */
    public function __construct(array $translations)
    {
        $this->guardTranslationArray($translations);
        $this->translations = $translations;
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    protected function guardTranslationArray(array $translations)
    {
        foreach ($translations as $language => $text) {
            $valid = preg_match('~^[a-z]{2}$~', $language) && is_string($text) && !empty($text);
            if (!$valid) {
                throw new InvalidArgumentException('Invalid translation array ' . json_encode($translations));
            }
        }
    }

    /**
     * @param self[] $languages
     * @return array
     */
    public static function toArray(array $languages): array
    {
        $result = [];
        foreach ($languages as $lang => $multiLang) {
            $result[$lang] = $multiLang->getTranslations();
        }
        return $result;
    }

    /**
     * @param array $languages
     * @param Exception $exception
     * @throws Exception
     */
    public static function guardLangArray(array $languages, Exception $exception)
    {
        foreach ($languages as $lang) {
            if (!($lang instanceof self)) {
                throw $exception;
            }
        }
    }

}