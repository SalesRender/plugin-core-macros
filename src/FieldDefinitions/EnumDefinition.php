<?php
/**
 * Created for lv-export-core.
 * Datetime: 02.07.2018 16:07
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use Exception;
use InvalidArgumentException;
use Leadvertex\External\Export\Core\Components\MultiLang;

class EnumDefinition extends FieldDefinition
{

    protected $enum;

    /**
     * ConfigDefinition constructor.
     * @param MultiLang $name
     * @param MultiLang $description
     * @param MultiLang[] $enum witch represent value => caption dropdown in different languages
     * array(
     *  '01' => new MultiLang('en' => 'January', 'ru' => 'Январь'),
     *  '02' => new MultiLang('en' => 'February', 'ru' => 'Февраль'),
     * )
     * @param string|int|float|bool|null $default value
     * @param bool $required is this field required
     * @throws Exception
     */
    public function __construct(MultiLang $name, MultiLang $description, array $enum, $default, bool $required)
    {
        parent::__construct($name, $description, $default, $required);
        MultiLang::guardLangArray($enum, new InvalidArgumentException('Invalid values array in ' . __CLASS__));
        $this->enum = $enum;
    }

    /**
     * @return string
     */
    public function definition(): string
    {
        return 'enum';
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['enum'] = MultiLang::toArray($this->enum);
        return $array;
    }

    /**
     * @param string|int|float|null $value
     * @return bool
     */
    public function validateValue($value): bool
    {
        if ($this->isRequired() && is_null($value)) {
            return false;
        }

        return isset($this->enum[$value]);
    }
}