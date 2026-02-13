# Plugin Core Macros

> Type-specific core framework for SalesRender macros plugins

## Overview

`salesrender/plugin-core-macros` is the core package for building **MACROS** type plugins on the SalesRender platform.
Macros plugins perform bulk operations on orders -- they process orders in batches, enabling mass data export, import,
field manipulation, status changes, and other automated workflows.

The key distinction of macros plugins from other types is **batch processing**: the ability to iterate over a set of
orders (defined by filters, sort, and pagination) and apply operations to each one, tracking progress, errors, and
results.

This package extends the base `salesrender/plugin-core` by:

- Adding **batch HTTP routes** (prepare, configure, run, status) via `WebAppFactory`
- Adding **batch CLI commands** (queue processing, batch handling) via `ConsoleAppFactory`
- Enabling **CORS** support by default in `WebAppFactory`
- Requiring the developer to implement `BatchHandlerInterface` for order processing logic

## Installation

```bash
composer require salesrender/plugin-core-macros
```

**Requirements:**
- PHP >= 7.4
- Extensions: `ext-json`

**Dependencies:**
- `salesrender/plugin-core` ^0.4.1
- `salesrender/plugin-component-purpose` ^2.0

## Architecture

### How This Core Extends plugin-core

`plugin-core-macros` provides two factory classes in the `SalesRender\Plugin\Core\Macros\Factories` namespace:

#### WebAppFactory

```php
namespace SalesRender\Plugin\Core\Macros\Factories;

class WebAppFactory extends \SalesRender\Plugin\Core\Factories\WebAppFactory
{
    public function build(): App
    {
        $this
            ->addCors()
            ->addBatchActions();

        return parent::build();
    }
}
```

The macros `WebAppFactory` automatically adds two feature sets before building:

1. **CORS support** (`addCors()`) -- enables cross-origin requests for all routes
2. **Batch actions** (`addBatchActions()`) -- registers all batch-related HTTP routes

The `addBatchActions()` method (defined in the parent `WebAppFactory`) registers the following routes:

| Method | Path | Action | Description |
|---|---|---|---|
| `POST` | `/protected/batch/prepare` | `BatchPrepareAction` | Creates a new batch with filters, sort, and arguments |
| `GET` | `/protected/forms/batch/{number}` | `GetBatchFormAction` | Returns batch step form (1-10) |
| `PUT` | `/protected/data/batch/{number}` | `PutBatchOptionsAction` | Saves batch step options |
| `POST` | `/protected/batch/run` | `BatchRunAction` | Starts batch execution |
| `GET` | `/process` | `ProcessAction` | Returns batch process status |
| `GET` | `/protected/autocomplete/{name}` | `AutocompleteAction` | Autocomplete suggestions |
| `GET` | `/protected/preview/table/{name}` | `TablePreviewAction` | Table preview data |
| `GET` | `/protected/preview/markdown/{name}` | `MarkdownPreviewAction` | Markdown preview |

#### ConsoleAppFactory

```php
namespace SalesRender\Plugin\Core\Macros\Factories;

class ConsoleAppFactory extends \SalesRender\Plugin\Core\Factories\ConsoleAppFactory
{
    public function build(): Application
    {
        $this->addBatchCommands();
        return parent::build();
    }
}
```

The macros `ConsoleAppFactory` adds **batch processing commands** via `addBatchCommands()`:

| Command | Class | Description |
|---|---|---|
| `batch:queue` | `BatchQueueCommand` | Picks up queued batches and spawns handler processes |
| `batch:handle` | `BatchHandleCommand` | Executes the batch handler for a specific batch |

These commands are also automatically registered as cron tasks (every minute) by the base `ConsoleAppFactory` when
a `BatchContainer` handler is configured.

### Batch Processing Flow

The batch processing lifecycle in a macros plugin follows these steps:

```
1. PREPARE    POST /protected/batch/prepare     -- Create batch with FSP (Filters, Sort, Pagination)
2. CONFIGURE  GET  /protected/forms/batch/{n}   -- Get configuration form for step N
              PUT  /protected/data/batch/{n}    -- Submit configuration for step N
              (repeat for steps 1-10 as needed)
3. RUN        POST /protected/batch/run         -- Start processing
4. PROCESS    (async via CLI)                   -- BatchHandler iterates orders
5. STATUS     GET  /process?id={id}             -- Check progress
```

### What the Developer Must Implement

1. **`BatchHandlerInterface`** -- the core processing logic that iterates over orders and performs operations
2. **Settings Form** -- a class extending `Form` for plugin configuration
3. **Batch Option Forms** -- one or more `Form` classes for batch step configuration (up to 10 steps)
4. **bootstrap.php** -- configuration file wiring everything together

## Getting Started: Creating a Macros Plugin

### Step 1: Project Setup

```bash
mkdir my-macros-plugin && cd my-macros-plugin
composer init --name="myvendor/my-macros-plugin" --type="project"
composer require salesrender/plugin-core-macros
composer require salesrender/plugin-component-purpose
```

Set up PSR-4 autoloading in `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "MyVendor\\Plugin\\Instance\\Macros\\": "src/"
    }
  }
}
```

Create the project directory structure:

```bash
mkdir -p src/Components src/Forms db public runtime
```

### Step 2: Bootstrap Configuration

Create `bootstrap.php` in the project root:

```php
<?php

use SalesRender\Plugin\Components\Batch\BatchContainer;
use SalesRender\Plugin\Components\Db\Components\Connector;
use SalesRender\Plugin\Components\Info\Developer;
use SalesRender\Plugin\Components\Info\Info;
use SalesRender\Plugin\Components\Info\PluginType;
use SalesRender\Plugin\Components\Purpose\MacrosPluginClass;
use SalesRender\Plugin\Components\Purpose\PluginEntity;
use SalesRender\Plugin\Components\Purpose\PluginPurpose;
use SalesRender\Plugin\Components\Settings\Settings;
use SalesRender\Plugin\Components\Translations\Translator;
use SalesRender\Plugin\Core\Actions\Upload\LocalUploadAction;
use SalesRender\Plugin\Core\Actions\Upload\UploadersContainer;
use Medoo\Medoo;
use MyVendor\Plugin\Instance\Macros\Components\MyHandler;
use MyVendor\Plugin\Instance\Macros\Forms\BatchOptionsForm;
use MyVendor\Plugin\Instance\Macros\Forms\SettingsForm;
use XAKEPEHOK\Path\Path;

require_once __DIR__ . '/vendor/autoload.php';

# 1. Configure DB
Connector::config(new Medoo([
    'database_type' => 'sqlite',
    'database_file' => Path::root()->down('db/database.db')
]));

# 2. Set plugin default language
Translator::config('ru_RU');

# 3. Set permitted file extensions and max sizes (optional)
UploadersContainer::addDefaultUploader(new LocalUploadAction([
    'jpg' => 1 * 1024 * 1024,
    'png' => 2 * 1024 * 1024,
]));

# 4. Configure info about plugin
Info::config(
    new PluginType(PluginType::MACROS),
    fn() => Translator::get('info', 'My Macros Plugin'),
    fn() => Translator::get('info', 'Description of my macros plugin'),
    new PluginPurpose(
        new MacrosPluginClass(MacrosPluginClass::CLASS_HANDLER),
        new PluginEntity(PluginEntity::ENTITY_ORDER)
    ),
    new Developer(
        'My Company',
        'support@example.com',
        'example.com',
    )
);

# 5. Configure settings form
Settings::setForm(fn($context) => new SettingsForm($context));

# 6. Configure batch forms and handler
BatchContainer::config(
    function (int $number) {
        switch ($number) {
            case 1: return new BatchOptionsForm();
            default: return null;
        }
    },
    new MyHandler()
);
```

Key points:
- Plugin type must be `PluginType::MACROS`
- The fourth argument to `Info::config()` is a `PluginPurpose` object specifying the plugin class and entity
- `BatchContainer::config()` accepts a callable returning batch step forms and a `BatchHandlerInterface` implementation
- Available `MacrosPluginClass` values: `CLASS_EXPORTER`, `CLASS_HANDLER`, `CLASS_IMPORTER`
- Available `PluginEntity` values: `ENTITY_ORDER`, `ENTITY_UNSPECIFIED`

### Step 3: Implement BatchHandlerInterface

Create `src/Components/MyHandler.php`:

```php
<?php

namespace MyVendor\Plugin\Instance\Macros\Components;

use SalesRender\Plugin\Components\Batch\Batch;
use SalesRender\Plugin\Components\Batch\BatchHandlerInterface;
use SalesRender\Plugin\Components\Batch\Process\Error;
use SalesRender\Plugin\Components\Batch\Process\Process;
use SalesRender\Plugin\Components\Settings\Settings;

class MyHandler implements BatchHandlerInterface
{
    public function __invoke(Process $process, Batch $batch)
    {
        // Guard: ensure settings are valid
        Settings::guardIntegrity();

        // Read settings
        $settings = Settings::find()->getData();

        // Read batch options from step 1
        $options = $batch->getOptions(1);

        // Get API client and FSP (Filters, Sort, Pagination) from the batch
        $apiClient = $batch->getApiClient();
        $fsp = $batch->getFsp();

        // Create an iterator to fetch orders from the API
        $iterator = new OrdersFetcherIterator(
            ['id', 'createdAt', 'status.id'],
            $apiClient,
            $fsp
        );

        // Initialize the process with the total count
        $process->initialize(count($iterator));

        // Process each order
        foreach ($iterator as $order) {
            try {
                // Your processing logic here
                $this->processOrder($order, $settings, $options);
                $process->handle();
            } catch (\Throwable $e) {
                $process->addError(new Error($e->getMessage(), $order['id']));
            }
            $process->save();
        }

        // Optional: post-processing phase
        $process->setState(Process::STATE_POST_PROCESSING);
        $process->save();

        // Finish with result (true = success, false = error, string = download URL)
        $process->finish(true);
        $process->save();
    }

    private function processOrder(array $order, $settings, $options): void
    {
        // Implement your order processing logic
    }
}
```

The `BatchHandlerInterface` has a single method:

```php
interface BatchHandlerInterface
{
    public function __invoke(Process $process, Batch $batch);
}
```

**Process** lifecycle methods:

| Method | Description |
|---|---|
| `$process->initialize(?int $count)` | Set total order count (null for unknown). Transitions state to `STATE_PROCESSING` |
| `$process->handle()` | Increment the handled counter |
| `$process->skip()` | Increment the skipped counter |
| `$process->addError(Error $error)` | Record an error (increments failed counter, stores last 20 errors) |
| `$process->setState(string $state)` | Set process state (`STATE_PROCESSING`, `STATE_POST_PROCESSING`) |
| `$process->finish($value)` | Mark as complete. `true` = success, `false` = error, `string` = result URL |
| `$process->terminate(Error $error)` | Terminate with a fatal error |
| `$process->save()` | Persist current state to database |

**Process** states: `STATE_SCHEDULED` -> `STATE_PROCESSING` -> `STATE_POST_PROCESSING` -> `STATE_ENDED`

**Batch** provides:

| Method | Description |
|---|---|
| `$batch->getApiClient()` | Returns the API client for querying SalesRender |
| `$batch->getFsp()` | Returns Filters, Sort, Pagination configuration |
| `$batch->getOptions(int $number)` | Returns form data for batch step N |

### Step 4: Create the Web Entry Point

Create `public/index.php`:

```php
<?php

use SalesRender\Plugin\Core\Macros\Factories\WebAppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$factory = new WebAppFactory();
$application = $factory->build();
$application->run();
```

Note: Unlike integration plugins, macros plugins typically do not need to add custom routes. The `WebAppFactory`
automatically registers all necessary batch routes.

### Step 5: Create the Console Entry Point

Create `console.php`:

```php
#!/usr/bin/env php
<?php

use SalesRender\Plugin\Core\Macros\Factories\ConsoleAppFactory;

require __DIR__ . '/vendor/autoload.php';

$factory = new ConsoleAppFactory();
$application = $factory->build();
$application->run();
```

### Step 6: Create the Settings Form

Create `src/Forms/SettingsForm.php`:

```php
<?php

namespace MyVendor\Plugin\Instance\Macros\Forms;

use SalesRender\Plugin\Components\Form\FieldDefinitions\BooleanDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Limit;
use SalesRender\Plugin\Components\Form\FieldDefinitions\ListOfEnum\Values\StaticValues;
use SalesRender\Plugin\Components\Form\FieldDefinitions\ListOfEnumDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\StringDefinition;
use SalesRender\Plugin\Components\Form\FieldGroup;
use SalesRender\Plugin\Components\Form\Form;
use SalesRender\Plugin\Components\Translations\Translator;

class SettingsForm extends Form
{
    public function __construct(array $context)
    {
        $this->setContext($context);
        parent::__construct(
            Translator::get('settings', 'Settings'),
            Translator::get('settings', 'Configure your macros plugin'),
            [
                'group_1' => new FieldGroup(
                    Translator::get('settings', 'General'),
                    null,
                    [
                        'fields' => new ListOfEnumDefinition(
                            Translator::get('settings', 'Columns'),
                            Translator::get('settings', 'Select data columns'),
                            function ($values) {
                                $errors = [];
                                if (!is_array($values) || count($values) < 1) {
                                    $errors[] = Translator::get('errors', 'Select at least one field');
                                }
                                return $errors;
                            },
                            new StaticValues([
                                'id' => ['title' => 'ID', 'group' => 'Order'],
                                'createdAt' => ['title' => 'Created At', 'group' => 'Order'],
                            ]),
                            new Limit(1, null),
                            ['id', 'createdAt']
                        ),
                    ]
                ),
            ],
            Translator::get('settings', 'Save')
        );
    }
}
```

### Step 7: Create Batch Options Form

Create `src/Forms/BatchOptionsForm.php`:

```php
<?php

namespace MyVendor\Plugin\Instance\Macros\Forms;

use SalesRender\Plugin\Components\Form\FieldDefinitions\IntegerDefinition;
use SalesRender\Plugin\Components\Form\FieldDefinitions\BooleanDefinition;
use SalesRender\Plugin\Components\Form\FieldGroup;
use SalesRender\Plugin\Components\Form\Form;
use SalesRender\Plugin\Components\Translations\Translator;

class BatchOptionsForm extends Form
{
    public function __construct()
    {
        parent::__construct(
            Translator::get('batch_options', 'Processing Options'),
            Translator::get('batch_options', 'Configure processing parameters'),
            [
                'options' => new FieldGroup(
                    Translator::get('batch_options', 'Options'),
                    null,
                    [
                        'skipErrors' => new BooleanDefinition(
                            Translator::get('batch_options', 'Skip errors'),
                            Translator::get('batch_options', 'Continue processing on error'),
                            function ($value) {
                                $errors = [];
                                if (!is_bool($value)) {
                                    $errors[] = 'Value must be boolean';
                                }
                                return $errors;
                            },
                            false
                        ),
                    ]
                ),
            ],
            Translator::get('batch_options', 'Start')
        );
    }
}
```

Batch option forms are displayed before running the batch (steps 1-10). Return `null` from the `BatchContainer::config`
callable for steps that do not require configuration.

### Step 8: Create the .env File

Create `.env`:

```env
LV_PLUGIN_DEBUG=1
LV_PLUGIN_PHP_BINARY=/usr/bin/php
LV_PLUGIN_QUEUE_LIMIT=1
LV_PLUGIN_SELF_URI=https://my-plugin.example.com/
```

### Step 9: Initialize and Deploy

```bash
# Create the database
php console.php db:create-tables

# Set up cron (required for batch processing)
# Add to crontab:
# * * * * * /usr/bin/php /path/to/my-plugin/console.php cron:run
```

The cron system is essential for macros plugins because batch processing runs asynchronously via the
`batch:queue` and `batch:handle` commands, which are automatically scheduled every minute.

## HTTP Routes

### Routes Added by Macros WebAppFactory

These routes are added by the macros `WebAppFactory` in addition to the base `plugin-core` routes:

| Method | Path | Auth | Description |
|---|---|---|---|
| `POST` | `/protected/batch/prepare` | JWT | Create a new batch with FSP. Returns 409 if batch already exists |
| `GET` | `/protected/forms/batch/{number}` | JWT | Get batch step form (1-10). Returns 425 if previous step incomplete |
| `PUT` | `/protected/data/batch/{number}` | JWT | Save batch step options. Returns 400 on validation errors |
| `POST` | `/protected/batch/run` | JWT | Start batch execution (sync in debug mode, async otherwise) |
| `GET` | `/process` | No | Get batch process status by `?id={processId}` |

### Routes Inherited from Base plugin-core

| Method | Path | Auth | Description |
|---|---|---|---|
| `GET` | `/info` | No | Plugin metadata |
| `PUT` | `/registration` | No | Plugin registration |
| `GET` | `/robots.txt` | No | Robots exclusion |
| `GET` | `/protected/forms/settings` | JWT | Settings form definition |
| `GET` | `/protected/data/settings` | JWT | Current settings data |
| `PUT` | `/protected/data/settings` | JWT | Save settings data |
| `GET` | `/protected/autocomplete/{name}` | JWT | Autocomplete handler |
| `GET` | `/protected/preview/table/{name}` | JWT | Table preview |
| `GET` | `/protected/preview/markdown/{name}` | JWT | Markdown preview |
| `POST` | `/protected/upload` | JWT | File upload |

CORS headers are enabled on all routes by default.

## CLI Commands

### Commands Added by Macros ConsoleAppFactory

| Command | Description |
|---|---|
| `batch:queue` | Picks up queued batches and spawns handler processes (runs every minute via cron) |
| `batch:handle` | Executes the `BatchHandlerInterface` for a specific batch |

### Commands Inherited from Base plugin-core

| Command | Description |
|---|---|
| `cron:run` | Runs all scheduled cron tasks |
| `directory:clean` | Cleans temporary directories |
| `db:create-tables` | Creates database tables |
| `db:clean-tables` | Cleans old database records |
| `lang:add` | Adds a new language |
| `lang:update` | Updates translations |
| `specialRequest:queue` | Processes special request queue |
| `specialRequest:handle` | Handles a special request |

### Auto-registered Cron Tasks

The macros `ConsoleAppFactory` automatically registers these cron tasks (every minute):

- `batch:queue` -- polls for queued batches and spawns handler processes
- `specialRequest:queue` -- polls for queued special requests

## Key Interfaces

### BatchHandlerInterface

```php
namespace SalesRender\Plugin\Components\Batch;

interface BatchHandlerInterface
{
    public function __invoke(Process $process, Batch $batch);
}
```

This is the central interface every macros plugin must implement. The handler receives:

- **`Process $process`** -- tracks progress, errors, and result of the batch operation
- **`Batch $batch`** -- provides access to the API client, FSP (Filters/Sort/Pagination), and batch step options

The handler must:
1. Call `$process->initialize($count)` to set the total order count
2. Iterate over orders, calling `$process->handle()`, `$process->skip()`, or `$process->addError()` for each
3. Call `$process->save()` after processing each order to persist progress
4. Call `$process->finish($result)` when done

### Process

```php
namespace SalesRender\Plugin\Components\Batch\Process;

class Process extends Model implements JsonSerializable
{
    const STATE_SCHEDULED = 'scheduled';
    const STATE_PROCESSING = 'processing';
    const STATE_POST_PROCESSING = 'post_processing';
    const STATE_ENDED = 'ended';

    public function initialize(?int $init): void;
    public function handle(): void;
    public function skip(): void;
    public function addError(Error $error): void;
    public function setState(string $state): void;
    public function finish($value): void;       // bool|int|string
    public function terminate(Error $error): void;
    public function save(): void;

    public function getHandledCount(): int;
    public function getSkippedCount(): int;
    public function getFailedCount(): int;
    public function getState(): string;
    public function getResult();
}
```

### Batch

```php
namespace SalesRender\Plugin\Components\Batch;

class Batch extends Model
{
    public function getApiClient(): ApiClient;
    public function getFsp(): FSP;
    public function getOptions(int $number): Dot;  // Returns batch step options as Dot notation object
}
```

### BatchContainer

```php
namespace SalesRender\Plugin\Components\Batch;

final class BatchContainer
{
    public static function config(callable $forms, BatchHandlerInterface $handler): void;
    public static function getForm(int $number, array $context = []): ?Form;
    public static function getHandler(): BatchHandlerInterface;
}
```

### PluginPurpose

```php
namespace SalesRender\Plugin\Components\Purpose;

class PluginPurpose implements JsonSerializable
{
    public function __construct(PluginClass $class, PluginEntity $entity);
}
```

**MacrosPluginClass** values:
- `MacrosPluginClass::CLASS_EXPORTER` -- plugin exports order data
- `MacrosPluginClass::CLASS_HANDLER` -- plugin processes/modifies orders
- `MacrosPluginClass::CLASS_IMPORTER` -- plugin imports external data into orders

**PluginEntity** values:
- `PluginEntity::ENTITY_ORDER` -- plugin operates on orders
- `PluginEntity::ENTITY_UNSPECIFIED` -- entity type is unspecified

### ActionInterface

```php
namespace SalesRender\Plugin\Core\Actions;

interface ActionInterface
{
    public function __invoke(ServerRequest $request, Response $response, array $args): Response;
}
```

Used for custom HTTP action handlers (not typically needed in macros plugins, but available).

## Example Plugin

The [plugin-macros-example](https://github.com/SalesRender/plugin-macros-example) is a comprehensive example
demonstrating all features of a macros plugin.

### Example Project Structure

```
plugin-macros-example/
    bootstrap.php              # Full configuration with batch, autocomplete, previews
    console.php                # CLI entry point
    example.env                # Environment variable template
    composer.json
    db/
    public/
        index.php              # Web entry point
        icon.png               # Plugin icon
        iframe/                # Static files for IFrame fields
    runtime/
    translations/              # Translation files (en_US.json, ru_RU.json)
    src/
        Autocomplete/
            Example.php             # AutocompleteInterface implementation
            ExampleWithDeps.php     # Autocomplete with dependencies
        Components/
            Columns.php             # Column definitions for order data
            ExampleHandler.php      # BatchHandlerInterface implementation
            FieldParser.php         # Field parsing utility
            OrdersFetcherIterator.php  # Order fetching via API
        Forms/
            SettingsForm.php              # Plugin settings form
            ResponseOptionsForm.php       # Batch step 1 form
            SecondResponseOptionsForm.php # Batch step 2 form
            PreviewOptionsForm.php        # Batch step 3 (preview) form
        MarkdownPreviewAction/
            MarkdownPreviewExample.php    # Markdown preview implementation
        TablePreviewAction/
            TablePreviewExample.php       # Table preview implementation
            TablePreviewExcel.php         # Excel table preview
    tests/                     # HTTP test files
```

### How the Example Plugin's BatchHandler Works

From `ExampleHandler.php`:

```php
class ExampleHandler implements BatchHandlerInterface
{
    public function __invoke(Process $process, Batch $batch)
    {
        Settings::guardIntegrity();

        // Read batch step options
        $delay = $batch->getOptions(1)->get('response_options.delay');

        // Create order iterator
        $iterator = new OrdersFetcherIterator(
            Columns::getQueryColumns($fields),
            $batch->getApiClient(),
            $batch->getFsp()
        );

        // Initialize with total count
        $process->initialize(count($iterator));

        // Process each order
        foreach ($iterator as $field) {
            $process->handle();  // or skip() or addError()
            $process->save();
            sleep($delay);
        }

        // Post-processing phase
        $process->setState(Process::STATE_POST_PROCESSING);
        $process->save();

        // Finish (true = success, string = URL, false = error)
        $process->finish(true);
        $process->save();
    }
}
```

## Dependencies

| Package | Version | Purpose |
|---|---|---|
| [`salesrender/plugin-core`](https://github.com/SalesRender/plugin-core) | ^0.4.1 | Base plugin framework (Slim 4 + Symfony Console) |
| [`salesrender/plugin-component-purpose`](https://github.com/SalesRender/plugin-component-purpose) | ^2.0 | Plugin purpose/class/entity definitions |

All transitive dependencies (Slim, Symfony Console, Medoo, batch components, etc.) come from `plugin-core`.

## See Also

- [plugin-core](https://github.com/SalesRender/plugin-core) -- Base framework for all SalesRender plugins
- [plugin-core-integration](https://github.com/SalesRender/plugin-core-integration) -- Core for integration plugins
- [plugin-core-logistic](https://github.com/SalesRender/plugin-core-logistic) -- Core for logistic plugins
- [plugin-core-chat](https://github.com/SalesRender/plugin-core-chat) -- Core for chat plugins
- [plugin-core-pbx](https://github.com/SalesRender/plugin-core-pbx) -- Core for PBX plugins
- [plugin-macros-example](https://github.com/SalesRender/plugin-macros-example) -- Example macros plugin
- [plugin-component-batch](https://github.com/SalesRender/plugin-component-batch) -- Batch processing components
- [plugin-component-purpose](https://github.com/SalesRender/plugin-component-purpose) -- Plugin purpose definitions
- [plugin-component-form](https://github.com/SalesRender/plugin-component-form) -- Form definitions and field types
- [plugin-component-settings](https://github.com/SalesRender/plugin-component-settings) -- Settings storage
- [plugin-component-db](https://github.com/SalesRender/plugin-component-db) -- Database abstraction
- [plugin-component-api-client](https://github.com/SalesRender/plugin-component-api-client) -- SalesRender API client
