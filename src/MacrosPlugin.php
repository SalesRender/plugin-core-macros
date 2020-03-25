<?php
/**
 * @author Timur Kasumov (aka XAKEPEHOK)
 * Datetime: 20.06.2019 17:43
 */

namespace Leadvertex\Plugin\Core\Macros;


use Leadvertex\Plugin\Components\ApiClient\ApiFilterSortPaginate;
use Leadvertex\Plugin\Components\Developer\Developer;
use Leadvertex\Plugin\Components\Process\Process;
use Leadvertex\Plugin\Components\Purpose\PluginPurpose;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Core\Macros\Components\AutocompleteInterface;
use Leadvertex\Plugin\Core\Macros\Models\Session;

abstract class MacrosPlugin
{

    /** @var self */
    private static $instance;

    /**
     * Example: ['en_US', 'ru_RU'];
     * @return array
     */
    abstract public static function getLanguages(): array;

    /**
     * Default language, that will be used when user language does't supported by your plugin
     * @return string, for example: 'en-US'
     */
    abstract public static function getDefaultLanguage(): string;

    /**
     * Should return human-friendly name of this plugin at requested language
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * Should return human-friendly description of this plugin at requested language
     * @return string
     */
    abstract public static function getDescription(): string;

    /**
     * @return PluginPurpose of entities, that can be handled by plugin
     */
    abstract public static function getPurpose(): PluginPurpose;

    /**
     * @return Developer
     */
    abstract public static function getDeveloper(): Developer;

    /**
     * Should return settings form for plugin configs
     * @return Form
     */
    abstract public function getSettingsForm(): Form;

    /**
     * Should return form for plugin options (before-handle form)
     * @param int $number
     * @return Form|null
     */
    abstract public function getRunForm(int $number): ?Form;

    /**
     * @param string $name
     * @return AutocompleteInterface
     */
    abstract public function autocomplete(string $name): ?AutocompleteInterface;

    /**
     * @param Process $process
     * @param ApiFilterSortPaginate|null $fsp
     * @return mixed
     */
    abstract public function run(Process $process, ?ApiFilterSortPaginate $fsp);

    public function setSession(Session $session)
    {
        if ($form = $this->getSettingsForm()) {
            $form->setData($session->getSettings()->getData());
        }

        for ($number = 1; $number <= 10; $number++) {
            if (!$session->getOptions($number)->isEmpty() && $form = $this->getRunForm($number)) {
                $form->setData($session->getOptions($number + 1));
                continue;
            }
            break;
        }
    }

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            $class = "\\Leadvertex\\Plugin\\Instance\\Macros\\Plugin";
            self::$instance = new $class();
        }

        return self::$instance;
    }

}