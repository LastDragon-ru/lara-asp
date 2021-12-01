<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Cron\CronExpression;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Date;
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
        $job      = $this->application->make($cronable);
        $config   = $this->configurator->config($job);
        $cron     = $config->get(CronableConfig::Cron);
        $enabled  = $config->get(CronableConfig::Enabled);
        $timezone = $config->get(CronableConfig::Timezone);

        // Enabled?
        if (!$enabled || $cron === null) {
            if ($this->isDue($cron)) {
                $this->jobDisabled($cronable, $job, $config);
            }

            return;
        }

        // Register
        $this
            ->schedule
            ->call(static function () use ($job): bool {
                return (bool) new PendingDispatch($job);
            })
            ->cron($cron)
            ->timezone($timezone)
            ->description($this->getJobName($cronable, $job, $config))
            ->after(function () use ($cronable, $job, $config): void {
                $this->jobDispatched($cronable, $job, $config);
            });
    }

    protected function getJobName(string $cronable, Cronable $job, QueueableConfig $config): string {
        return method_exists($job, 'displayName') ? $job->displayName() : $cronable;
    }

    protected function isDue(?string $cron): bool {
        try {
            return $cron && (new CronExpression($cron))->isDue(Date::now());
        } catch (Exception) {
            return false;
        }
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
