# Queue Helpers

This package provides additional capabilities for queued jobs and queued listeners like multilevel configuration support, job overriding (very useful for package development to provide base implementation and allow the application to extend it), easy define for cron jobs, and DI in constructor support.

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^10.0.0` |   `HEAD ⋯ 2.1.0`   |
|  | `^9.21.0` |   `HEAD ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)

# Installation

1. Run

   ```shell
    composer require lastdragon-ru/lara-asp-queue
    ```

2. Overwrite default event Dispatcher by adding following code into `bootstrap/app.php` (before all others singletons):

   ```php
   $app->singleton('events', \LastDragon_ru\LaraASP\Queue\EventsDispatcher::class);
   ```

   This is required if you want use configuration/DI for queued Listeners. Please see <https://github.com/laravel/framework/issues/25272> for reason.

# Configuration

To add the configuration for job/listener/mailable you just need extends one the [base classes](https://github.com/LastDragon-ru/lara-asp/tree/master/packages/queue/src/Queueables):

[include:example]: ./docs/Examples/Job.php
[//]: # (start: 3cd947b41ad3328b5336549ba1d9af58d6a6913a6eb2bead617fc5af102f47a2)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace App\Jobs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Queue\QueueableConfigurator;
use LastDragon_ru\LaraASP\Queue\Queueables\Job;
use Override;

class MyJobWithConfig extends Job {
    /**
     * Default config.
     *
     * @inheritDoc
     */
    #[Override]
    public function getQueueConfig(): array {
        return [
                'queue'    => 'queue',
                'settings' => [
                    'expire' => '18 hours',
                ],
            ] + parent::getQueueConfig();
    }

    public function __invoke(QueueableConfigurator $configurator): void {
        // This is how we can get access to the actual config inside `handle`
        $config = $configurator->config($this);
        $expire = Cast::toString($config->setting('expire'));
        $expire = Date::now()->sub($expire);

        Model::query()
            ->where('updated_at', '<', $expire)
            ->delete();
    }
}
```

[//]: # (end: 3cd947b41ad3328b5336549ba1d9af58d6a6913a6eb2bead617fc5af102f47a2)

Configurations have the following priority  (last win):

* own properties (`$this->connection`, `$this->queue`, etc)
* own config from `getQueueConfig()`
* app's config (`queue.queueables.<class>` from `config/queue.php` if present)
* `onConnection()`, `onQueue()`, etc calls

Thus, you can easily set settings for your jobs in app config, for example, we can set the `expire` setting on `8 hours`:

[include:example]: ./docs/Examples/JobConfig.php
[//]: # (start: 1c17e54b9495db6c881e2873b2d93d6a5cad28896fb5791b6d2176dbac425db9)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use App\Jobs\MyJobWithConfig;

// config/queue.php

return [
    // .....

    /*
    |--------------------------------------------------------------------------
    | Queueables Configuration
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of custom queue jobs.
    |
    */
    'queueables' => [
        MyJobWithConfig::class => [
            'settings' => [
                'expire' => '8 hours',
            ],
        ],
    ],
];
```

[//]: # (end: 1c17e54b9495db6c881e2873b2d93d6a5cad28896fb5791b6d2176dbac425db9)

# Cron jobs

Creating the cron jobs is similar. They just have two additional settings:

[include:example]: ./docs/Examples/CronJob.php
[//]: # (start: 35b6bcd4d6008a44fe3958797034035afe226ce97b0769934b552ff0403229ba)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace App\Jobs;

use LastDragon_ru\LaraASP\Queue\Queueables\CronJob;
use Override;

class MyCronJob extends CronJob {
    /**
     * @inheritDoc
     */
    #[Override]
    public function getQueueConfig(): array {
        return [
                'cron'    => '0 * * * *', // Cron expression
                'enabled' => true,        // Status (`false` will disable the job)
            ] + parent::getQueueConfig();
    }

    public function __invoke(): void {
        // ....
    }
}
```

[//]: # (end: 35b6bcd4d6008a44fe3958797034035afe226ce97b0769934b552ff0403229ba)

But the registration of the jobs a slightly different. For `Kernel` you should use following way:

[include:example]: ./docs/Examples/CronJobRegistractionKernel.php
[//]: # (start: 66050e315130d2df632b9f7b4ba31cb6dd05ba89be1c58b386733a37dc90b8fa)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace App\Console;

use App\Jobs\MyCronJob;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use LastDragon_ru\LaraASP\Queue\Concerns\ConsoleKernelWithSchedule;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use Override;

use function base_path;

class Kernel extends ConsoleKernel {
    // !!! Add this trait
    use ConsoleKernelWithSchedule;

    // !!! Add this property and put all cron jobs inside
    /**
     * The application's command schedule.
     *
     * @var list<class-string<Cronable>>
     */
    protected array $schedule = [
        MyCronJob::class,
    ];

    /**
     * Register the commands for the application.
     */
    #[Override]
    protected function commands(): void {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
```

[//]: # (end: 66050e315130d2df632b9f7b4ba31cb6dd05ba89be1c58b386733a37dc90b8fa)

And for package providers:

[include:example]: ./docs/Examples/CronJobRegistractionProvider.php
[//]: # (start: a896db9ade3e0cf5196026e5985b14ebbd559c3b45974c29810e137fbd5c7a7d)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator;

use App\Jobs\MyCronJob;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Queue\Concerns\ProviderWithSchedule;

class Provider extends ServiceProvider {
    use ProviderWithSchedule;

    public function boot(): void {
        $this->bootSchedule(
            // Put all cron jobs provided in the package here
            MyCronJob::class,
        );
    }
}
```

[//]: # (end: a896db9ade3e0cf5196026e5985b14ebbd559c3b45974c29810e137fbd5c7a7d)

Finally, the package also discloses all settings in the job description:

```text
$ php artisan schedule:list

+---------+-------------+------------------------------------------------------------------------+---------------------+
| Command | Interval    | Description                                                            | Next Due            |
+---------+-------------+------------------------------------------------------------------------+---------------------+
|         | 0 0 * * *   | App\Jobs\JobsCleanupCronJob                                            | 2021-03-14 00:00:00 |
|         |             | {"queue":"default","enabled":true,"settings":{"expire":"18 hours"}}    |                     |
|         | */5 * * * * | App\Jobs\SiteLogsCleanupCronJob                                        | 2021-03-13 06:40:00 |
|         |             | {"queue":"default","enabled":true,"settings":{"expire":"30 days"}}     |                     |
+---------+-------------+------------------------------------------------------------------------+---------------------+
```

# Overriding package Jobs

The most interesting and useful thing for package developers is the ability to extend all package's jobs in the application. For example, our package provides the `DoSomethingPackageJob`, its settings can be easily changed through the config, but can we extend it in the app? Yes!

First, we are no need additional actions for `CronJob`, but should use `Container::make()` for `Job` and `Mails`:

[include:example]: ./docs/Examples/OverridingDispatch.php
[//]: # (start: 5d62194b843f0f973ce2be1a8092dd5a1c2e25384902ab56535dc7db2f89eb42)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use Illuminate\Container\Container;
use Package\Jobs\DoSomethingPackageJob;

// Use
Container::getInstance()->make(DoSomethingPackageJob::class)->dispatch();

// Instead of
// @phpstan-ignore-next-line
DoSomethingPackageJob::dispatch();
```

[//]: # (end: 5d62194b843f0f973ce2be1a8092dd5a1c2e25384902ab56535dc7db2f89eb42)

then inside the app

[include:example]: ./docs/Examples/OverridingExtend.php
[//]: # (start: 793aa204932b1906e880117a27c0dafe7e25f4c2b1c5df93d97846c6b92ae265)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace App\Jobs;

use Override;
use Package\Jobs\DoSomethingPackageJob;

class DoSomethingAppJob extends DoSomethingPackageJob {
    #[Override]
    public function __invoke(): void {
        // our implementation
    }
}
```

[//]: # (end: 793aa204932b1906e880117a27c0dafe7e25f4c2b1c5df93d97846c6b92ae265)

and finally, register it:

[include:example]: ./docs/Examples/OverridingRegister.php
[//]: # (start: 73fc6e94eac317140747a3b6b1cab5b01ab1b58929a9895f227f086951b97f85)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace App\Providers;

use App\Jobs\DoSomethingAppJob;
use Illuminate\Support\ServiceProvider;
use Override;
use Package\Jobs\DoSomethingPackageJob;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void {
        $this->app->bind(DoSomethingAppJob::class, DoSomethingPackageJob::class);
    }
}
```

[//]: # (end: 73fc6e94eac317140747a3b6b1cab5b01ab1b58929a9895f227f086951b97f85)

🥳

The `CustomUpdateSomethingJob` will use the same settings name in `config/queue.php` as `UpdateSomethingJob`. Sometimes you may want to create a new job with its own config, in this case, you should break the config chain:

[include:example]: ./docs/Examples/OverridingOwnConfig.php
[//]: # (start: 36fcaf761a2bf7347c56fa167669ac6832d3383ba7dfba74c30f3880723737a9)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace App\Jobs;

use LastDragon_ru\LaraASP\Queue\Concerns\WithConfig;
use Override;
use Package\Jobs\DoSomethingPackageJob;

class DoSomethingAppJob extends DoSomethingPackageJob {
    use WithConfig; // Indicates that the job has its own config

    #[Override]
    public function __invoke(): void {
        // our implementation
    }
}
```

[//]: # (end: 36fcaf761a2bf7347c56fa167669ac6832d3383ba7dfba74c30f3880723737a9)

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: 057ec3a599c54447e95d6dd2e9f0f6a6621d9eb75446a5e5e471ba9b2f414b89)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 057ec3a599c54447e95d6dd2e9f0f6a6621d9eb75446a5e5e471ba9b2f414b89)
