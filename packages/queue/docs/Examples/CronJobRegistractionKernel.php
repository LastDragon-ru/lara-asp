<?php declare(strict_types = 1);

namespace App\Console;

use App\Jobs\MyCronJob;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use LastDragon_ru\LaraASP\Queue\Concerns\ConsoleKernelWithSchedule;
use LastDragon_ru\LaraASP\Queue\Contracts\Cronable;

use function base_path;

class Kernel extends ConsoleKernel {
    // !!! Add this trait
    use ConsoleKernelWithSchedule;

    // !!! Add this property and put all cron jobs inside
    /**
     * The application's command schedule.
     *
     * @var list<class-string<Cronable>>
     */
    protected array $schedule = [
        MyCronJob::class,
    ];

    /**
     * Register the commands for the application.
     */
    protected function commands(): void {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
