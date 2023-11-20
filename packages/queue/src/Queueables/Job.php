<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Queueables;

use AllowDynamicProperties;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use LastDragon_ru\LaraASP\Queue\Concerns\Dispatchable;
use LastDragon_ru\LaraASP\Queue\Concerns\WithConfig;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use Override;

#[AllowDynamicProperties]
abstract class Job implements ShouldQueue, ConfigurableQueueable {
    use Queueable;
    use SerializesModels;
    use Dispatchable;
    use WithConfig;

    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getQueueConfig(): array {
        return [];
    }
}
