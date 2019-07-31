<?php
/**
 * Created for plugin-export-core
 * Datetime: 31.07.2019 19:16
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Exporter\Core\Apps;


use Dotenv\Dotenv;

trait EnvironmentTrait
{

    private function loadEnvironment(string $appDir)
    {
        $env = Dotenv::create($appDir);
        $env->required([
            'LV_EXPORT_RUNTIME_DIR',
            'LV_EXPORT_PUBLIC_DIR',
            'LV_EXPORT_PUBLIC_URL',
            'LV_EXPORT_CONSOLE_SCRIPT',
            'LV_EXPORT_DEBUG',
        ]);
        $env->load();
    }

}