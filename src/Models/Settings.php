<?php
/**
 * Created for plugin-core
 * Datetime: 26.02.2020 15:08
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Core\Macros\Models;


use Leadvertex\Plugin\Components\Db\Model;
use Leadvertex\Plugin\Components\Form\FormData;

/**
 * Class Settings
 * @package Leadvertex\Plugin\Core\Macros\Models
 *
 * @property $data FormData
 */
class Settings extends Model
{

    public function __construct(string $id = null, string $feature = '')
    {
        parent::__construct($id, $feature);
        $this->data = new FormData();
    }

    public function getData(): FormData
    {
        return $this->data;
    }

    public function setData(FormData $data)
    {
        $this->data = $data;
    }

}