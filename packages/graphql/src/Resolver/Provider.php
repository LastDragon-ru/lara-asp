<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Resolver;

use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Support\Contracts\ProvidesResolver;

class Provider extends ServiceProvider {
    public function register(): void {
        parent::register();

        $this->app->bind(ProvidesResolver::class, ResolverProvider::class);
    }
}
