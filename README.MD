Example of index.php 
```php
<?php
use Leadvertex\External\Export\Core\Apps\WebApplication;
use Webmozart\PathUtil\Path;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new WebApplication(
    Path::canonicalize(__DIR__ . '/../runtime'),
    Path::canonicalize(__DIR__ . '/compiled')
);

$app->run();
```

Example of console.php 
```php
#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use Leadvertex\External\Export\Core\Apps\ConsoleApplication;
use Webmozart\PathUtil\Path;

$application = new ConsoleApplication(
    Path::canonicalize(__DIR__ . '/runtime'),
    Path::canonicalize(__DIR__ . '/web/compiled')
);
$application->run();
```