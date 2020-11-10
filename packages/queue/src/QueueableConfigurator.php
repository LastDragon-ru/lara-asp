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
        $global     = $this->app->make('config');
        $config     = null;
        $properties = $this->getQueueableProperties();

        if ($queueable instanceof Mailable) {
            $config = new MailableConfig($global, $queueable, $properties);
        } elseif ($queueable instanceof Cronable) {
            $config = new CronableConfig($global, $queueable, $properties);
        } else {
            $config = new QueueableConfig($global, $queueable, $properties);
        }

        return $config;
    }

    public function configure(ConfigurableQueueable $queueable): void {
        $config     = $this->config($queueable);
        $properties = array_keys($this->getQueueableProperties());

        foreach ($properties as $property) {
            $value = $config->get($property);

            if (!is_null($value)) {
                $queueable->{$property} = $value;
            }
        }
    }

    protected function getQueueableProperties(): array {
        // TODO [laravel] [update] Check available queue properties.
        /** SEE {@link https://laravel.com/docs/8.x/queues} */
        return [
            'connection'              => null,  // Connection name for the job
            'queue'                   => null,  // Queue name for the job
            'timeout'                 => null,  // Number of seconds the job can run
            'tries'                   => null,  // Number of times the job may be attempted
            'maxExceptions'           => null,  // Number of exceptions allowed for the job before fail
            'backoff'                 => null,  // Retry delay for the failed job
            'deleteWhenMissingModels' => null,  // Allow deleting the job if the model does not exist anymore
        ];
    }
}
