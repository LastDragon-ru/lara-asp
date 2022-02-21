<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bus\PendingDispatch;
use LastDragon_ru\LaraASP\Queue\Configs\CronableConfig;
use LastDragon_ru\LaraASP\Queue\Configs\QueueableConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use LogicException;
use Psr\Log\LoggerInterface;

use function method_exists;
use function sprintf;

class CronableRegistrator {
    public function __construct(
        protected Application $application,
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
    public function register(Schedule $schedule, string $cronable): void {
        // Registration only makes sense when the app running in console.
        if (!$this->application->runningInConsole()) {
            throw new LogicException('The application is not running in console.');
        }

        // Prepare
        /** @var Cronable $job */
        $job      = $this->application->make($cronable);
        $config   = $this->configurator->config($job);
        $cron     = $config->get(CronableConfig::Cron);
        $timezone = $config->get(CronableConfig::Timezone);

        // Cron?
        if ($cron === null) {
            return;
        }

        // Register
        $schedule
            ->call(function () use ($cronable, $job, $config): bool {
                $this->dispatch($cronable, $job, $config);

                return true;
            })
            ->cron($cron)
            ->timezone($timezone)
            ->description($this->getJobDescription($cronable, $job, $config))
            ->after(function () use ($cronable, $job, $config): void {
                $this->jobDispatched($cronable, $job, $config);
            });
    }

    protected function dispatch(string $cronable, Cronable $job, QueueableConfig $config): bool {
        // Disabled?
        if (!$config->get(CronableConfig::Enabled)) {
            $this->jobDisabled($cronable, $job, $config);

            return false;
        }

        // Dispatch
        return (bool) new PendingDispatch($job);
    }

    protected function getJobName(string $cronable, Cronable $job, QueueableConfig $config): string {
        return method_exists($job, 'displayName') ? $job->displayName() : $cronable;
    }

    protected function getJobDescription(string $cronable, Cronable $job, QueueableConfig $config): string {
        $description = $this->getJobName($cronable, $job, $config);
        $enabled     = $config->get(CronableConfig::Enabled);

        if (!$enabled) {
            $description = "{$description} (disabled)";
        }

        return $description;
    }

    protected function jobDisabled(string $cronable, Cronable $job, QueueableConfig $config): void {
        $this->logger->info(
            sprintf('Cron job `%s` is disabled.', $this->getJobName($cronable, $job, $config)),
        );
    }

    /**
     * @param class-string<Cronable> $cronable
     */
    protected function jobDispatched(string $cronable, Cronable $job, QueueableConfig $config): void {
        $this->logger->info(
            sprintf('Cron job `%s` dispatched successfully.', $this->getJobName($cronable, $job, $config)),
        );
    }
}
