<?php declare(strict_types = 1);

namespace App\Jobs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Queue\QueueableConfigurator;
use LastDragon_ru\LaraASP\Queue\Queueables\Job;

class MyJobWithConfig extends Job {
    /**
     * Default config.
     *
     * @inheritDoc
     */
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
