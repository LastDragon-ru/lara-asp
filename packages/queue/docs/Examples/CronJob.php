<?php declare(strict_types = 1);

namespace App\Jobs;

use LastDragon_ru\LaraASP\Queue\Queueables\CronJob;
use Override;

class MyCronJob extends CronJob {
    /**
     * @inheritDoc
     */
    #[Override]
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
