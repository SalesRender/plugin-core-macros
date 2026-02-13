# Plugin Core Macros

> Типоспецифичный фреймворк для macros-плагинов SalesRender

## Обзор

`salesrender/plugin-core-macros` -- пакет ядра для создания плагинов типа **MACROS** на платформе SalesRender.
Macros-плагины выполняют массовые операции над заказами -- они обрабатывают заказы пакетами, обеспечивая массовый
экспорт данных, импорт, манипуляцию полями, смену статусов и другие автоматизированные процессы.

Ключевое отличие macros-плагинов от других типов -- **пакетная обработка** (batch processing): возможность итерировать
по набору заказов (определяемому фильтрами, сортировкой и пагинацией) и применять операции к каждому из них с
отслеживанием прогресса, ошибок и результатов.

Данный пакет расширяет базовый `salesrender/plugin-core` следующим образом:

- Добавляет **HTTP-маршруты для batch-операций** (подготовка, конфигурация, запуск, статус) через `WebAppFactory`
- Добавляет **CLI-команды для пакетной обработки** (обработка очереди, выполнение batch) через `ConsoleAppFactory`
- Включает поддержку **CORS** по умолчанию в `WebAppFactory`
- Требует от разработчика реализации `BatchHandlerInterface` для логики обработки заказов

## Установка

```bash
composer require salesrender/plugin-core-macros
```

**Требования:**
- PHP >= 7.4
- Расширения: `ext-json`

**Зависимости:**
- `salesrender/plugin-core` ^0.4.1
- `salesrender/plugin-component-purpose` ^2.0

## Архитектура

### Как данное ядро расширяет plugin-core

`plugin-core-macros` предоставляет два класса фабрик в пространстве имен
`SalesRender\Plugin\Core\Macros\Factories`:

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

Macros-версия `WebAppFactory` автоматически добавляет два набора функциональности перед сборкой:

1. **Поддержка CORS** (`addCors()`) -- включает кросс-доменные запросы для всех маршрутов
2. **Batch-действия** (`addBatchActions()`) -- регистрирует все HTTP-маршруты, связанные с пакетной обработкой

Метод `addBatchActions()` (определен в родительском `WebAppFactory`) регистрирует следующие маршруты:

| Метод | Путь | Action | Описание |
|---|---|---|---|
| `POST` | `/protected/batch/prepare` | `BatchPrepareAction` | Создает новый batch с фильтрами, сортировкой и аргументами |
| `GET` | `/protected/forms/batch/{number}` | `GetBatchFormAction` | Возвращает форму шага batch (1-10) |
| `PUT` | `/protected/data/batch/{number}` | `PutBatchOptionsAction` | Сохраняет параметры шага batch |
| `POST` | `/protected/batch/run` | `BatchRunAction` | Запускает выполнение batch |
| `GET` | `/process` | `ProcessAction` | Возвращает статус процесса batch |
| `GET` | `/protected/autocomplete/{name}` | `AutocompleteAction` | Подсказки автодополнения |
| `GET` | `/protected/preview/table/{name}` | `TablePreviewAction` | Предпросмотр таблицы |
| `GET` | `/protected/preview/markdown/{name}` | `MarkdownPreviewAction` | Предпросмотр markdown |

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

Macros-версия `ConsoleAppFactory` добавляет **команды пакетной обработки** через `addBatchCommands()`:

| Команда | Класс | Описание |
|---|---|---|
| `batch:queue` | `BatchQueueCommand` | Забирает batch из очереди и порождает процессы-обработчики |
| `batch:handle` | `BatchHandleCommand` | Выполняет batch handler для конкретного batch |

Эти команды также автоматически регистрируются как cron-задачи (каждую минуту) базовым `ConsoleAppFactory`,
когда в `BatchContainer` сконфигурирован обработчик.

### Жизненный цикл пакетной обработки

Жизненный цикл пакетной обработки в macros-плагине проходит следующие этапы:

```
1. PREPARE    POST /protected/batch/prepare     -- Создание batch с FSP (Filters, Sort, Pagination)
2. CONFIGURE  GET  /protected/forms/batch/{n}   -- Получение формы конфигурации для шага N
              PUT  /protected/data/batch/{n}    -- Отправка конфигурации для шага N
              (повторить для шагов 1-10 при необходимости)
3. RUN        POST /protected/batch/run         -- Запуск обработки
4. PROCESS    (асинхронно через CLI)            -- BatchHandler итерирует заказы
5. STATUS     GET  /process?id={id}             -- Проверка прогресса
```

### Что должен реализовать разработчик

1. **`BatchHandlerInterface`** -- основная логика обработки, итерирующая заказы и выполняющая операции
2. **Форма настроек** -- класс, наследующий `Form`, для конфигурации плагина
3. **Формы параметров batch** -- один или несколько классов `Form` для конфигурации шагов batch (до 10 шагов)
4. **bootstrap.php** -- файл конфигурации, связывающий все компоненты воедино

## Начало работы: Создание macros-плагина

### Шаг 1: Настройка проекта

```bash
mkdir my-macros-plugin && cd my-macros-plugin
composer init --name="myvendor/my-macros-plugin" --type="project"
composer require salesrender/plugin-core-macros
composer require salesrender/plugin-component-purpose
```

Настройте PSR-4 автозагрузку в `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "MyVendor\\Plugin\\Instance\\Macros\\": "src/"
    }
  }
}
```

Создайте структуру директорий проекта:

```bash
mkdir -p src/Components src/Forms db public runtime
```

### Шаг 2: Конфигурация bootstrap

Создайте `bootstrap.php` в корне проекта:

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

# 1. Конфигурация БД
Connector::config(new Medoo([
    'database_type' => 'sqlite',
    'database_file' => Path::root()->down('db/database.db')
]));

# 2. Установка языка плагина по умолчанию
Translator::config('ru_RU');

# 3. Настройка допустимых расширений файлов и максимальных размеров (необязательно)
UploadersContainer::addDefaultUploader(new LocalUploadAction([
    'jpg' => 1 * 1024 * 1024,
    'png' => 2 * 1024 * 1024,
]));

# 4. Конфигурация информации о плагине
Info::config(
    new PluginType(PluginType::MACROS),
    fn() => Translator::get('info', 'Мой macros-плагин'),
    fn() => Translator::get('info', 'Описание моего macros-плагина'),
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

# 5. Конфигурация формы настроек
Settings::setForm(fn($context) => new SettingsForm($context));

# 6. Конфигурация форм batch и обработчика
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

Ключевые моменты:
- Тип плагина должен быть `PluginType::MACROS`
- Четвертый аргумент `Info::config()` -- объект `PluginPurpose`, определяющий класс и сущность плагина
- `BatchContainer::config()` принимает callable, возвращающий формы шагов batch, и реализацию `BatchHandlerInterface`
- Доступные значения `MacrosPluginClass`: `CLASS_EXPORTER`, `CLASS_HANDLER`, `CLASS_IMPORTER`
- Доступные значения `PluginEntity`: `ENTITY_ORDER`, `ENTITY_UNSPECIFIED`

### Шаг 3: Реализация BatchHandlerInterface

Создайте `src/Components/MyHandler.php`:

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
        // Проверка целостности настроек
        Settings::guardIntegrity();

        // Чтение настроек
        $settings = Settings::find()->getData();

        // Чтение параметров batch шага 1
        $options = $batch->getOptions(1);

        // Получение API-клиента и FSP (Filters, Sort, Pagination) из batch
        $apiClient = $batch->getApiClient();
        $fsp = $batch->getFsp();

        // Создание итератора для получения заказов через API
        $iterator = new OrdersFetcherIterator(
            ['id', 'createdAt', 'status.id'],
            $apiClient,
            $fsp
        );

        // Инициализация процесса с общим количеством
        $process->initialize(count($iterator));

        // Обработка каждого заказа
        foreach ($iterator as $order) {
            try {
                // Ваша логика обработки
                $this->processOrder($order, $settings, $options);
                $process->handle();
            } catch (\Throwable $e) {
                $process->addError(new Error($e->getMessage(), $order['id']));
            }
            $process->save();
        }

        // Необязательно: фаза постобработки
        $process->setState(Process::STATE_POST_PROCESSING);
        $process->save();

        // Завершение с результатом (true = успех, false = ошибка, string = URL для скачивания)
        $process->finish(true);
        $process->save();
    }

    private function processOrder(array $order, $settings, $options): void
    {
        // Реализуйте логику обработки заказа
    }
}
```

`BatchHandlerInterface` имеет единственный метод:

```php
interface BatchHandlerInterface
{
    public function __invoke(Process $process, Batch $batch);
}
```

**Методы жизненного цикла Process:**

| Метод | Описание |
|---|---|
| `$process->initialize(?int $count)` | Установить общее количество заказов (null для неизвестного). Переводит состояние в `STATE_PROCESSING` |
| `$process->handle()` | Увеличить счетчик обработанных |
| `$process->skip()` | Увеличить счетчик пропущенных |
| `$process->addError(Error $error)` | Записать ошибку (увеличивает счетчик ошибок, хранит последние 20 ошибок) |
| `$process->setState(string $state)` | Установить состояние процесса (`STATE_PROCESSING`, `STATE_POST_PROCESSING`) |
| `$process->finish($value)` | Отметить как завершенный. `true` = успех, `false` = ошибка, `string` = URL результата |
| `$process->terminate(Error $error)` | Прервать с фатальной ошибкой |
| `$process->save()` | Сохранить текущее состояние в базу данных |

**Состояния Process:** `STATE_SCHEDULED` -> `STATE_PROCESSING` -> `STATE_POST_PROCESSING` -> `STATE_ENDED`

**Batch предоставляет:**

| Метод | Описание |
|---|---|
| `$batch->getApiClient()` | Возвращает API-клиент для запросов к SalesRender |
| `$batch->getFsp()` | Возвращает конфигурацию Filters, Sort, Pagination |
| `$batch->getOptions(int $number)` | Возвращает данные формы для шага batch N |

### Шаг 4: Создание точки входа для web

Создайте `public/index.php`:

```php
<?php

use SalesRender\Plugin\Core\Macros\Factories\WebAppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$factory = new WebAppFactory();
$application = $factory->build();
$application->run();
```

Примечание: В отличие от интеграционных плагинов, macros-плагинам обычно не нужно добавлять пользовательские маршруты.
`WebAppFactory` автоматически регистрирует все необходимые batch-маршруты.

### Шаг 5: Создание точки входа для консоли

Создайте `console.php`:

```php
#!/usr/bin/env php
<?php

use SalesRender\Plugin\Core\Macros\Factories\ConsoleAppFactory;

require __DIR__ . '/vendor/autoload.php';

$factory = new ConsoleAppFactory();
$application = $factory->build();
$application->run();
```

### Шаг 6: Создание формы настроек

Создайте `src/Forms/SettingsForm.php`:

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
            Translator::get('settings', 'Настройки'),
            Translator::get('settings', 'Настройте ваш macros-плагин'),
            [
                'group_1' => new FieldGroup(
                    Translator::get('settings', 'Основные'),
                    null,
                    [
                        'fields' => new ListOfEnumDefinition(
                            Translator::get('settings', 'Столбцы'),
                            Translator::get('settings', 'Выберите столбцы данных'),
                            function ($values) {
                                $errors = [];
                                if (!is_array($values) || count($values) < 1) {
                                    $errors[] = Translator::get('errors', 'Выберите хотя бы одно поле');
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
            Translator::get('settings', 'Сохранить')
        );
    }
}
```

### Шаг 7: Создание формы параметров batch

Создайте `src/Forms/BatchOptionsForm.php`:

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
            Translator::get('batch_options', 'Параметры обработки'),
            Translator::get('batch_options', 'Настройте параметры обработки'),
            [
                'options' => new FieldGroup(
                    Translator::get('batch_options', 'Параметры'),
                    null,
                    [
                        'skipErrors' => new BooleanDefinition(
                            Translator::get('batch_options', 'Пропускать ошибки'),
                            Translator::get('batch_options', 'Продолжать обработку при ошибке'),
                            function ($value) {
                                $errors = [];
                                if (!is_bool($value)) {
                                    $errors[] = 'Значение должно быть boolean';
                                }
                                return $errors;
                            },
                            false
                        ),
                    ]
                ),
            ],
            Translator::get('batch_options', 'Запуск')
        );
    }
}
```

Формы параметров batch отображаются перед запуском пакетной обработки (шаги 1-10). Возвращайте `null` из callable
в `BatchContainer::config` для шагов, не требующих конфигурации.

### Шаг 8: Создание файла .env

Создайте `.env`:

```env
LV_PLUGIN_DEBUG=1
LV_PLUGIN_PHP_BINARY=/usr/bin/php
LV_PLUGIN_QUEUE_LIMIT=1
LV_PLUGIN_SELF_URI=https://my-plugin.example.com/
```

### Шаг 9: Инициализация и развертывание

```bash
# Создание базы данных
php console.php db:create-tables

# Настройка cron (обязательно для пакетной обработки)
# Добавьте в crontab:
# * * * * * /usr/bin/php /path/to/my-plugin/console.php cron:run
```

Система cron необходима для macros-плагинов, поскольку пакетная обработка выполняется асинхронно через
команды `batch:queue` и `batch:handle`, которые автоматически планируются на запуск каждую минуту.

## HTTP-маршруты

### Маршруты, добавляемые macros WebAppFactory

Эти маршруты добавляются macros-версией `WebAppFactory` в дополнение к базовым маршрутам `plugin-core`:

| Метод | Путь | Аутентификация | Описание |
|---|---|---|---|
| `POST` | `/protected/batch/prepare` | JWT | Создание нового batch с FSP. Возвращает 409, если batch уже существует |
| `GET` | `/protected/forms/batch/{number}` | JWT | Получение формы шага batch (1-10). Возвращает 425, если предыдущий шаг не завершен |
| `PUT` | `/protected/data/batch/{number}` | JWT | Сохранение параметров шага batch. Возвращает 400 при ошибках валидации |
| `POST` | `/protected/batch/run` | JWT | Запуск выполнения batch (синхронно в debug-режиме, асинхронно иначе) |
| `GET` | `/process` | Нет | Получение статуса процесса batch по `?id={processId}` |

### Маршруты, унаследованные от базового plugin-core

| Метод | Путь | Аутентификация | Описание |
|---|---|---|---|
| `GET` | `/info` | Нет | Метаданные плагина |
| `PUT` | `/registration` | Нет | Регистрация плагина |
| `GET` | `/robots.txt` | Нет | Блокировка индексации |
| `GET` | `/protected/forms/settings` | JWT | Определение формы настроек |
| `GET` | `/protected/data/settings` | JWT | Текущие данные настроек |
| `PUT` | `/protected/data/settings` | JWT | Сохранение данных настроек |
| `GET` | `/protected/autocomplete/{name}` | JWT | Обработчик автодополнения |
| `GET` | `/protected/preview/table/{name}` | JWT | Предпросмотр таблицы |
| `GET` | `/protected/preview/markdown/{name}` | JWT | Предпросмотр markdown |
| `POST` | `/protected/upload` | JWT | Загрузка файлов |

CORS-заголовки включены на всех маршрутах по умолчанию.

## CLI-команды

### Команды, добавляемые macros ConsoleAppFactory

| Команда | Описание |
|---|---|
| `batch:queue` | Забирает batch из очереди и порождает процессы-обработчики (запуск каждую минуту через cron) |
| `batch:handle` | Выполняет `BatchHandlerInterface` для конкретного batch |

### Команды, унаследованные от базового plugin-core

| Команда | Описание |
|---|---|
| `cron:run` | Запускает все запланированные cron-задачи |
| `directory:clean` | Очищает временные директории |
| `db:create-tables` | Создает таблицы базы данных |
| `db:clean-tables` | Очищает устаревшие записи |
| `lang:add` | Добавляет новый язык |
| `lang:update` | Обновляет переводы |
| `specialRequest:queue` | Обрабатывает очередь специальных запросов |
| `specialRequest:handle` | Обрабатывает специальный запрос |

### Автоматически регистрируемые cron-задачи

Macros `ConsoleAppFactory` автоматически регистрирует следующие cron-задачи (каждую минуту):

- `batch:queue` -- опрашивает очередь batch и порождает процессы-обработчики
- `specialRequest:queue` -- опрашивает очередь специальных запросов

## Ключевые интерфейсы

### BatchHandlerInterface

```php
namespace SalesRender\Plugin\Components\Batch;

interface BatchHandlerInterface
{
    public function __invoke(Process $process, Batch $batch);
}
```

Центральный интерфейс, который должен реализовать каждый macros-плагин. Обработчик получает:

- **`Process $process`** -- отслеживает прогресс, ошибки и результат пакетной операции
- **`Batch $batch`** -- предоставляет доступ к API-клиенту, FSP (Filters/Sort/Pagination) и параметрам шагов batch

Обработчик должен:
1. Вызвать `$process->initialize($count)` для установки общего количества заказов
2. Итерировать по заказам, вызывая `$process->handle()`, `$process->skip()` или `$process->addError()` для каждого
3. Вызывать `$process->save()` после обработки каждого заказа для сохранения прогресса
4. Вызвать `$process->finish($result)` по завершении

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
    public function getOptions(int $number): Dot;  // Возвращает параметры шага batch как Dot-объект
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

**Значения MacrosPluginClass:**
- `MacrosPluginClass::CLASS_EXPORTER` -- плагин экспортирует данные заказов
- `MacrosPluginClass::CLASS_HANDLER` -- плагин обрабатывает/модифицирует заказы
- `MacrosPluginClass::CLASS_IMPORTER` -- плагин импортирует внешние данные в заказы

**Значения PluginEntity:**
- `PluginEntity::ENTITY_ORDER` -- плагин оперирует заказами
- `PluginEntity::ENTITY_UNSPECIFIED` -- тип сущности не указан

### ActionInterface

```php
namespace SalesRender\Plugin\Core\Actions;

interface ActionInterface
{
    public function __invoke(ServerRequest $request, Response $response, array $args): Response;
}
```

Используется для пользовательских HTTP-обработчиков (обычно не требуется в macros-плагинах, но доступен).

## Пример плагина

[plugin-macros-example](https://github.com/SalesRender/plugin-macros-example) -- комплексный пример,
демонстрирующий все возможности macros-плагина.

### Структура примера

```
plugin-macros-example/
    bootstrap.php              # Полная конфигурация с batch, автодополнением, предпросмотром
    console.php                # Точка входа CLI
    example.env                # Шаблон переменных окружения
    composer.json
    db/
    public/
        index.php              # Точка входа web
        icon.png               # Иконка плагина
        iframe/                # Статические файлы для IFrame-полей
    runtime/
    translations/              # Файлы переводов (en_US.json, ru_RU.json)
    src/
        Autocomplete/
            Example.php             # Реализация AutocompleteInterface
            ExampleWithDeps.php     # Автодополнение с зависимостями
        Components/
            Columns.php             # Определения столбцов для данных заказов
            ExampleHandler.php      # Реализация BatchHandlerInterface
            FieldParser.php         # Утилита для парсинга полей
            OrdersFetcherIterator.php  # Получение заказов через API
        Forms/
            SettingsForm.php              # Форма настроек плагина
            ResponseOptionsForm.php       # Форма шага batch 1
            SecondResponseOptionsForm.php # Форма шага batch 2
            PreviewOptionsForm.php        # Форма шага batch 3 (предпросмотр)
        MarkdownPreviewAction/
            MarkdownPreviewExample.php    # Реализация предпросмотра markdown
        TablePreviewAction/
            TablePreviewExample.php       # Реализация предпросмотра таблицы
            TablePreviewExcel.php         # Предпросмотр таблицы Excel
    tests/                     # Файлы HTTP-тестов
```

### Как работает BatchHandler в примере

Из `ExampleHandler.php`:

```php
class ExampleHandler implements BatchHandlerInterface
{
    public function __invoke(Process $process, Batch $batch)
    {
        Settings::guardIntegrity();

        // Чтение параметров шага batch
        $delay = $batch->getOptions(1)->get('response_options.delay');

        // Создание итератора заказов
        $iterator = new OrdersFetcherIterator(
            Columns::getQueryColumns($fields),
            $batch->getApiClient(),
            $batch->getFsp()
        );

        // Инициализация с общим количеством
        $process->initialize(count($iterator));

        // Обработка каждого заказа
        foreach ($iterator as $field) {
            $process->handle();  // или skip() или addError()
            $process->save();
            sleep($delay);
        }

        // Фаза постобработки
        $process->setState(Process::STATE_POST_PROCESSING);
        $process->save();

        // Завершение (true = успех, string = URL, false = ошибка)
        $process->finish(true);
        $process->save();
    }
}
```

## Зависимости

| Пакет | Версия | Назначение |
|---|---|---|
| [`salesrender/plugin-core`](https://github.com/SalesRender/plugin-core) | ^0.4.1 | Базовый фреймворк плагинов (Slim 4 + Symfony Console) |
| [`salesrender/plugin-component-purpose`](https://github.com/SalesRender/plugin-component-purpose) | ^2.0 | Определения назначения/класса/сущности плагина |

Все транзитивные зависимости (Slim, Symfony Console, Medoo, batch-компоненты и т.д.) поступают из `plugin-core`.

## Смотрите также

- [plugin-core](https://github.com/SalesRender/plugin-core) -- Базовый фреймворк для всех плагинов SalesRender
- [plugin-core-integration](https://github.com/SalesRender/plugin-core-integration) -- Ядро для интеграционных плагинов
- [plugin-core-logistic](https://github.com/SalesRender/plugin-core-logistic) -- Ядро для логистических плагинов
- [plugin-core-chat](https://github.com/SalesRender/plugin-core-chat) -- Ядро для chat-плагинов
- [plugin-core-pbx](https://github.com/SalesRender/plugin-core-pbx) -- Ядро для PBX-плагинов
- [plugin-macros-example](https://github.com/SalesRender/plugin-macros-example) -- Пример macros-плагина
- [plugin-component-batch](https://github.com/SalesRender/plugin-component-batch) -- Компоненты пакетной обработки
- [plugin-component-purpose](https://github.com/SalesRender/plugin-component-purpose) -- Определения назначения плагина
- [plugin-component-form](https://github.com/SalesRender/plugin-component-form) -- Определения форм и типы полей
- [plugin-component-settings](https://github.com/SalesRender/plugin-component-settings) -- Хранилище настроек
- [plugin-component-db](https://github.com/SalesRender/plugin-component-db) -- Абстракция базы данных
- [plugin-component-api-client](https://github.com/SalesRender/plugin-component-api-client) -- API-клиент SalesRender
