<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Queue\Configs\CronableConfig;
use LastDragon_ru\LaraASP\Queue\Configs\QueueableConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use LogicException;
use Psr\Log\LoggerInterface;

class CronableRegistrator {
    protected Application           $app;
    protected LoggerInterface       $logger;
    protected Schedule              $schedule;
    protected QueueableConfigurator $configurator;

    public function __construct(
        Application $app,
        Schedule $schedule,
        QueueableConfigurator $configurator,
        LoggerInterface $logger
    ) {
        $this->app          = $app;
        $this->logger       = $logger;
        $this->schedule     = $schedule;
        $this->configurator = $configurator;
    }

    public function register(string $cronable): void {
        // Cronable?
        if (!is_subclass_of($cronable, Cronable::class, true)) {
            throw new InvalidArgumentException(sprintf('The $cronable must implement %s.', Cronable::class));
        }

        // Registration only makes sense when the app running in console.
        if (!$this->app->runningInConsole()) {
            throw new LogicException('The application is not running in console.');
        }

        // Registration is not possible until boot
        if (!$this->app->isBooted()) {
            throw new LogicException('The application is not yet booted.');
        }

        // Enabled?
        /** @var \LastDragon_ru\LaraASP\Queue\Contracts\Cronable $job */
        $job        = $this->app->make($cronable);
        $config     = $this->configurator->config($job);
        $cron       = $config->get(CronableConfig::Cron);
        $debug      = $config->get(CronableConfig::Debug);
        $enabled    = $config->get(CronableConfig::Enabled);
        $properties = [
            'cronable' => $cronable,
            'actual'   => get_class($job),
        ];

        if (!$cron || !$enabled) {
            if ($debug) {
                $this->logger->info('Cron job is disabled.', array_merge($properties, [
                    'enabled' => $enabled,
                    'cron'    => $cron,
                ]));
            }

            return;
        }

        // Register
        $this
            ->schedule
            ->job($job)
            ->cron($cron)
            ->description($this->getDescription($cronable, $job, $config))
            ->after(function () use ($debug, $properties) {
                if ($debug) {
                    $this->logger->info('Cron job was dispatched successfully', $properties);
                }
            });
    }

    protected function getDescription(string $cronable, Cronable $job, QueueableConfig $config): string {
        $actual      = get_class($job);
        $settings    = $this->getDescriptionSettings($config);
        $overridden  = $cronable !== $actual;
        $description = $cronable;

        if ($overridden && $this->app->make('config')->get('app.debug')) {
            $description .= " (overridden by {$actual})";
        }

        if ($settings) {
            $description .= "\n".json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $description;
    }

    protected function getDescriptionSettings(QueueableConfig $config): array {
        $settings = array_filter($config->all());

        unset($settings['cron']);

        return $settings;
    }
}
