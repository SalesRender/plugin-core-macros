<?php
/**
 * @author Timur Kasumov (aka XAKEPEHOK)
 * Datetime: 20.06.2019 17:43
 */

namespace Leadvertex\Plugin\Exporter\Core;


use Leadvertex\Plugin\Components\ApiClient\ApiClient;
use Leadvertex\Plugin\Components\Developer\Developer;
use Leadvertex\Plugin\Components\Purpose\PluginEntity;
use Leadvertex\Plugin\Exporter\Core\Components\GenerateParams;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Components\I18n\I18nInterface;

interface ExporterInterface
{

    public function __construct(ApiClient $apiClient, string $runtimeDir, string $publicDir, string $publicUrl);

    /**
     * @see \Leadvertex\Plugin\Components\I18n\I18nInterface::getLanguages
     * @return array
     */
    public static function getLanguages(): array;

    /**
     * Default language, that will be used when user language does't supported by your plugin
     * @see \Leadvertex\Plugin\Components\I18n\I18nInterface
     * @see https://en.wikipedia.org/wiki/ISO_639-1
     * @return string alpha-2 language code, for example: 'en', 'ru', etc
     */
    public static function getDefaultLanguage(): string;

    /**
     * Should return human-friendly name of this exporter
     * @return I18nInterface
     */
    public static function getName(): I18nInterface;

    /**
     * Should return human-friendly description of this exporter
     * @return I18nInterface
     */
    public static function getDescription(): I18nInterface;

    /**
     * @return PluginEntity of entities, that can be exported by plugin
     */
    public function getEntity(): PluginEntity;

    /**
     * @return Developer
     */
    public function getDeveloper(): Developer;

    /**
     * Should return scheme of exporter configs
     * @return Form
     */
    public function getForm(): Form;

    /**
     * @param GenerateParams $params
     * @return mixed
     */
    public function generate(GenerateParams $params);

}