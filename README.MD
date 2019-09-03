Examples
=====
See [plugin-export-excel](https://github.com/leadvertex/plugin-export-excel) for examples

Usage
---
Define a few constants in `const.php`
```php
<?php 
require_once __DIR__ . '/vendor/autoload.php';

define('LV_PLUGIN_DIR', __DIR__);
define('LV_PLUGIN_URL', 'http://example.com/');
define('LV_PLUGIN_DEBUG', false);
```

Example of `public/index.php`
```php
<?php
require_once __DIR__ . '/../const.php';

$factory = new \Leadvertex\Plugin\Handler\Factories\AppFactory();
$application = $factory->web();
$application->run();
```

Example of `console.php`
```php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/const.php';

$factory = new \Leadvertex\Plugin\Handler\Factories\AppFactory();
$application = $factory->console();
$application->run();
```