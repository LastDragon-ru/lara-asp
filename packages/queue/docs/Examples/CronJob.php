<?php declare(strict_types = 1);

namespace App\Jobs;

use LastDragon_ru\LaraASP\Queue\Queueables\CronJob;

class MyCronJob extends CronJob {
    /**
     * @inheritDoc
     */
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
