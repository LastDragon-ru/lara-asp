<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider {
    public function register(): void {
        parent::register();

        $this->callAfterResolving(
            Repository::class,
            static function (Repository $config): void {
                $config->set('lighthouse.schema_path', __DIR__.'/schema.graphql');
                $config->set('lighthouse.guards', null);
            },
        );
    }
}
