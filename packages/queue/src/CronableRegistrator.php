<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Cron\CronExpression;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Queue\Configs\CronableConfig;
use LastDragon_ru\LaraASP\Queue\Configs\QueueableConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use LogicException;
use Psr\Log\LoggerInterface;

use function array_filter;
use function json_encode;

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
     * @param class-string<Cronable> $cronable
     */
    public function register(string $cronable): void {
        // Registration only makes sense when the app running in console.
        if (!$this->application->runningInConsole()) {
            throw new LogicException('The application is not running in console.');
        }

        // Prepare
        /** @var Cronable $job */
        $job     = $this->application->make($cronable);
        $config  = $this->configurator->config($job);
        $cron    = $config->get(CronableConfig::Cron);
        $enabled = $config->get(CronableConfig::Enabled);

        // Enabled?
        if (!$enabled) {
            if ($this->isDue($cron)) {
                $this->jobDisabled($cronable, $job, $config);
            }

            return;
        }

        // Should?
        if ($this->isLocked($job) && $this->isDue($cron)) {
            $this->jobLocked($cronable, $job, $config);
        }

        // Register
        $this
            ->schedule
            ->job($job)
            ->cron($cron)
            ->description($this->getDescription($cronable, $job, $config))
            ->after(function () use ($cronable, $job, $config): void {
                $this->jobDispatched($cronable, $job, $config);
            });
    }

    /**
     * @param class-string<Cronable> $cronable
     */
    protected function getDescription(string $cronable, Cronable $job, QueueableConfig $config): string {
        $actual      = $job::class;
        $settings    = $this->getDescriptionSettings($config);
        $description = $cronable;

        if ($cronable !== $actual) {
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

    protected function isDue(?string $cron): bool {
        try {
            return $cron && (new CronExpression($cron))->isDue(Date::now());
        } catch (Exception) {
            return false;
        }
    }

    protected function isLocked(Cronable $cronable): bool {
        return !(new class($cronable) extends PendingDispatch {
            public function __destruct() {
                // empty
            }

            public function shouldDispatch(): bool {
                return parent::shouldDispatch();
            }
        })->shouldDispatch();
    }

    protected function jobLocked(string $cronable, Cronable $job, QueueableConfig $config): void {
        $this->logger->notice('Cron job is locked.', $this->getLogContext($cronable, $job, $config));
    }

    protected function jobDisabled(string $cronable, Cronable $job, QueueableConfig $config): void {
        $this->logger->info('Cron job is disabled.', $this->getLogContext($cronable, $job, $config));
    }

    /**
     * @param class-string<Cronable> $cronable
     */
    protected function jobDispatched(string $cronable, Cronable $job, QueueableConfig $config): void {
        $this->logger->info('Cron job was dispatched successfully.', $this->getLogContext($cronable, $job, $config));
    }

    /**
     * @return array<string,mixed>
     */
    protected function getLogContext(string $cronable, Cronable $job, QueueableConfig $config): array {
        return [
            'cronable' => $cronable,
            'actual'   => $job::class,
            'cron'     => $config->get(CronableConfig::Cron),
        ];
    }
}
