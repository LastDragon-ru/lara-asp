<?php declare(strict_types = 1);

namespace App\Providers;

use App\Jobs\DoSomethingAppJob;
use Illuminate\Support\ServiceProvider;
use Package\Jobs\DoSomethingPackageJob;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        $this->app->bind(DoSomethingAppJob::class, DoSomethingPackageJob::class);
    }
}
