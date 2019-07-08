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

define('LV_EXPORT_RUNTIME_DIR', Path::canonicalize(__DIR__ . '/runtime'));
define('LV_EXPORT_PUBLIC_DIR', Path::canonicalize(__DIR__ . '/public/compiled'));
define('LV_EXPORT_PUBLIC_URL', 'http://example.com/compiled');
define('LV_EXPORT_CONSOLE_SCRIPT', Path::canonicalize(__DIR__ . '/console.php'));
define('LV_EXPORT_DEBUG', false);
```

Example of `index.php`
```php
<?php
require_once __DIR__ . '/../const.php';

$app = new Leadvertex\Plugin\Export\Core\Apps\WebApplication(
    LV_EXPORT_RUNTIME_DIR,
    LV_EXPORT_PUBLIC_DIR,
    LV_EXPORT_PUBLIC_URL,
    LV_EXPORT_CONSOLE_SCRIPT,
    LV_EXPORT_DEBUG
);

$app->run();
```

Example of `console.php`
```php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/const.php';

$application = new Leadvertex\Plugin\Export\Core\Apps\ConsoleApplication(
    LV_EXPORT_RUNTIME_DIR,
    LV_EXPORT_PUBLIC_DIR
);
$application->run();
```