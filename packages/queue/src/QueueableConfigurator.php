<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Mail\Mailable;
use LastDragon_ru\LaraASP\Queue\Configs\CronableConfig;
use LastDragon_ru\LaraASP\Queue\Configs\MailableConfig;
use LastDragon_ru\LaraASP\Queue\Configs\QueueableConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;

/**
 * Queueable configurator.
 */
class QueueableConfigurator {
    protected Application $app;

    public function __constructor(Application $app) {
        $this->app = $app;
    }

    public function config(ConfigurableQueueable $queueable): QueueableConfig {
        $global = $this->app->make('config');
        $config = null;

        if ($queueable instanceof Mailable) {
            $config = new MailableConfig($global, $queueable);
        } elseif ($queueable instanceof Cronable) {
            $config = new CronableConfig($global, $queueable);
        } else {
            $config = new QueueableConfig($global, $queueable);
        }

        return $config;
    }

    public function configure(ConfigurableQueueable $queueable): void {
        $config     = $this->config($queueable);
        $properties = $this->getQueueableProperties();

        foreach ($properties as $property) {
            $value = $config->get($property);

            if (!is_null($value)) {
                $queueable->{$property} = $value;
            }
        }
    }

    protected function getQueueableProperties(): array {
        // TODO [laravel] [update] Check available queue properties.
        return [
            'connection',
            'queue',
            'timeout',
            'tries',
            'maxExceptions',
            'backoff',
            'deleteWhenMissingModels',
        ];
    }
}
