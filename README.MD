Examples
=====
See [plugin-export-excel](https://github.com/leadvertex/plugin-export-excel) for examples

Usage
---
Define a few constants in `const.php`
```php
<?php 
require_once __DIR__ . '/vendor/autoload.php';
use Webmozart\PathUtil\Path;

define('LV_PLUGIN_DIR_RUNTIME', Path::canonicalize(__DIR__ . '/runtime'));
define('LV_PLUGIN_DIR_PUBLIC', Path::canonicalize(__DIR__ . '/public/compiled'));
define('LV_PLUGIN_URL_PUBLIC', 'http://example.com/compiled');
define('LV_PLUGIN_CONSOLE_SCRIPT', Path::canonicalize(__DIR__ . '/console.php'));
define('LV_PLUGIN_DEBUG', false);
```

Example of `index.php`
```php
<?php
require_once __DIR__ . '/../const.php';

/** @var App $app */
$app = \Leadvertex\Plugin\Handlers\Factories\AppFactory::web();
$app->run();
```

Example of `console.php`
```php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/const.php';

/** @var Application $application */
$application = \Leadvertex\Plugin\Handlers\Factories\AppFactory::console();
$application->run();
```