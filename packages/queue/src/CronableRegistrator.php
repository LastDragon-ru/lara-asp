<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bus\PendingDispatch;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Queue\Configs\CronableConfig;
use LastDragon_ru\LaraASP\Queue\Configs\QueueableConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use LogicException;
use Psr\Log\LoggerInterface;

use function array_filter;
use function array_merge;
use function is_subclass_of;
use function json_encode;
use function sprintf;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class CronableRegistrator {
    public function __construct(
        protected Application $application,
        protected Repository $config,
        protected Schedule $schedule,
        protected QueueableConfigurator $configurator,
        protected LoggerInterface $logger,
    ) {
        // empty
    }

    /**
     * Register {@link \LastDragon_ru\LaraASP\Queue\Contracts\Cronable} as
     * scheduled job. This method shouldn't be used until the app booted.
     *
     * @param string $cronable {@link \LastDragon_ru\LaraASP\Queue\Contracts\Cronable} class
     */
    public function register(string $cronable): void {
        // Cronable?
        if (!is_subclass_of($cronable, Cronable::class, true)) {
            throw new InvalidArgumentException(
                sprintf('The `$cronable` must implement %s.', Cronable::class),
            );
        }

        // Registration only makes sense when the app running in console.
        if (!$this->application->runningInConsole()) {
            throw new LogicException('The application is not running in console.');
        }

        // Enabled?
        /** @var \LastDragon_ru\LaraASP\Queue\Contracts\Cronable $job */
        $job        = $this->application->make($cronable);
        $config     = $this->configurator->config($job);
        $cron       = $config->get(CronableConfig::Cron);
        $debug      = $config->get(CronableConfig::Debug);
        $enabled    = $config->get(CronableConfig::Enabled);
        $properties = [
            'cronable' => $cronable,
            'actual'   => $job::class,
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

        // Should?
        if (!$this->shouldDispatch($job)) {
            if ($debug) {
                $this->logger->info('Cron job already dispatched.', $properties);
            }
        }

        // Register
        $this
            ->schedule
            ->job($job)
            ->cron($cron)
            ->description($this->getDescription($cronable, $job, $config))
            ->after(function () use ($debug, $properties): void {
                if ($debug) {
                    $this->logger->info('Cron job was dispatched successfully.', $properties);
                }
            });
    }

    protected function getDescription(string $cronable, Cronable $job, QueueableConfig $config): string {
        $actual      = $job::class;
        $settings    = $this->getDescriptionSettings($config);
        $overridden  = $cronable !== $actual;
        $description = $cronable;

        if ($overridden && $this->config->get('app.debug')) {
            $description .= " (overridden by {$actual})";
        }

        if ($settings) {
            $description .= "\n".json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $description;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDescriptionSettings(QueueableConfig $config): array {
        $settings = array_filter($config->all());

        unset($settings['cron']);

        return $settings;
    }

    protected function shouldDispatch(Cronable $cronable): bool {
        return (new class($cronable) extends PendingDispatch {
            public function __destruct() {
                // empty
            }

            public function shouldDispatch(): bool {
                return parent::shouldDispatch();
            }
        })->shouldDispatch();
    }
}
