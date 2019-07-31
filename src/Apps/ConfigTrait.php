<?php
/**
 * Created for plugin-export-core
 * Datetime: 31.07.2019 19:16
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Exporter\Core\Apps;


use RuntimeException;

trait ConfigTrait
{

    private function checkConfig()
    {
        $constants = [
            'LV_EXPORT_RUNTIME_DIR',
            'LV_EXPORT_PUBLIC_DIR',
            'LV_EXPORT_PUBLIC_URL',
            'LV_EXPORT_CONSOLE_SCRIPT',
            'LV_EXPORT_DEBUG',
        ];

        foreach ($constants as $constant) {
            if (!defined($constant)) {
                throw new RuntimeException("Constant {$constant} is not defined");
            }
        }
    }

}