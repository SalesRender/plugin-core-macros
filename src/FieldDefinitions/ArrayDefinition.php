<?php
/**
 * Created for lv-export-core.
 * Datetime: 02.07.2018 15:37
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use Exception;
use InvalidArgumentException;
use Leadvertex\External\Export\Core\Components\MultiLang;

class ArrayDefinition extends FieldDefinition
{

    /**
     * @var MultiLang[]
     */
    protected $enum;

    /**
     * ArrayDefinition constructor.
     * @param MultiLang $name
     * @param MultiLang $description
     * @param MultiLang[] $enum witch represent value => caption dropdown in different languages
     * array(
     *  'jan' => new MultiLang('en' => 'January', 'ru' => 'Январь'),
     *  'feb' => new MultiLang('en' => 'February', 'ru' => 'Февраль'),
     * )
     * @param $default
     * @param bool $required
     * @throws Exception
     */
    public function __construct(MultiLang $name, MultiLang $description, array $enum, $default, bool $required)
    {
        parent::__construct($name, $description, $default, $required);
        MultiLang::guardLangArray($enum, new InvalidArgumentException('Invalid values array in ' . __CLASS__));
        $this->enum = array_values($enum);
    }

    /**
     * @return string
     */
    public function definition(): string
    {
        return 'array';
    }

    /**
     * @param $array
     * @return bool
     */
    public function validateValue($array): bool
    {
        $isEmpty = is_null($array) || (is_array($array) && empty($array));
        if ($this->isRequired() && $isEmpty) {
            return false;
        }

        $isFlatArray = is_array($array) && (count($array) !== count($array, COUNT_RECURSIVE));
        if (!$isFlatArray) {
            return false;
        }

        //Invalid values
        foreach ($array as $value) {
            if (!isset($this->enum[$value])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getEnum(): array
    {
        return $this->enum;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['enum'] = MultiLang::toArray($this->enum);
        return $array;
    }
}