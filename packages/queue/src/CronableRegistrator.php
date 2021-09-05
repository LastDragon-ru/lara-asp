<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Cron\CronExpression;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Queue\Configs\CronableConfig;
use LastDragon_ru\LaraASP\Queue\Configs\QueueableConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;
use LogicException;
use Psr\Log\LoggerInterface;

use function array_filter;
use function json_encode;
use function method_exists;
use function sprintf;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

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

    protected function jobDisabled(string $cronable, Cronable $job, QueueableConfig $config): void {
        $this->logger->info(
            sprintf('Cron job `%s` is disabled.', $this->getJobName($cronable, $job, $config)),
            $this->getLogContext($cronable, $job, $config),
        );
    }

    /**
     * @param class-string<Cronable> $cronable
     */
    protected function jobDispatched(string $cronable, Cronable $job, QueueableConfig $config): void {
        $this->logger->info(
            sprintf('Cron job `%s` dispatched successfully.', $this->getJobName($cronable, $job, $config)),
            $this->getLogContext($cronable, $job, $config),
        );
    }

    protected function getJobName(string $cronable, Cronable $job, QueueableConfig $config): string {
        return method_exists($job, 'displayName') ? $job->displayName() : $cronable;
    }

    /**
     * @return array<string,mixed>
     */
    protected function getLogContext(string $cronable, Cronable $job, QueueableConfig $config): array {
        return [
            'cronable' => $cronable,
            'class'    => $job::class,
            'cron'     => $config->get(CronableConfig::Cron),
        ];
    }
}
