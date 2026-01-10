<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use Override;
use ReflectionClass;

/**
 * @internal
 */
class Provider extends ServiceProvider {
    #[Override]
    public function register(): void {
        parent::register();

        $this->callAfterResolving(
            Repository::class,
            static function (Repository $config): void {
                $config->set('lighthouse.schema_path', __DIR__.'/schema.graphql');
                $config->set('lighthouse.guards', null);
                $config->set('lighthouse.namespaces.models', [
                    (new ReflectionClass(TestObject::class))->getNamespaceName(),
                ]);
            },
        );
    }
}
