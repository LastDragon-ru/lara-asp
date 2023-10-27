<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Migrator;

use App\Jobs\MyCronJob;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Queue\Concerns\ProviderWithSchedule;

class Provider extends ServiceProvider {
    use ProviderWithSchedule;

    public function boot(): void {
        $this->bootSchedule(
            // Put all cron jobs provided in the package here
            MyCronJob::class,
        );
    }
}
