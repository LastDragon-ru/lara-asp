<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithTranslations;
use LastDragon_ru\LaraASP\GraphQL\Helpers\EnumHelper;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Metadata;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Repository as MetadataRepository;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Usage;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByDirective;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_slice;
use function explode;
use function implode;
use function is_int;

class Provider extends ServiceProvider {
    use ProviderWithConfig;
    use ProviderWithTranslations;

    public function boot(Dispatcher $dispatcher): void {
        $this->bootConfig();
        $this->bootTranslations();
        $this->bootDirectives($dispatcher);
        $this->bootEnums();
    }

    public function register(): void {
        parent::register();

        $this->registerSearchByDirective();
    }

    protected function bootDirectives(Dispatcher $dispatcher): void {
        $dispatcher->listen(
            RegisterDirectiveNamespaces::class,
            static function (): string {
                return implode('\\', array_slice(explode('\\', SearchByDirective::class), 0, -1));
            },
        );
        $dispatcher->listen(
            RegisterDirectiveNamespaces::class,
            static function (): string {
                return implode('\\', array_slice(explode('\\', SortByDirective::class), 0, -1));
            },
        );
    }

    protected function registerSearchByDirective(): void {
        $this->app->singleton(MetadataRepository::class);
        $this->app->bind(Metadata::class, function (Application $app): Metadata {
            $usage    = $app->make(Usage::class);
            $scalars  = (array) $app->make(Repository::class)->get("{$this->getName()}.search_by.scalars");
            $metadata = new Metadata($app, $usage);

            foreach ($scalars as $scalar => $operators) {
                $metadata->addScalar($scalar, $operators);
            }

            return $metadata;
        });
    }

    protected function bootEnums(): void {
        $registry = $this->app->make(TypeRegistry::class);
        $enums    = (array) $this->app->make(Repository::class)->get("{$this->getName()}.enums");

        foreach ($enums as $name => $enum) {
            if (is_int($name)) {
                $name = null;
            }

            if ($enum) {
                $registry->register(EnumHelper::getType($enum, $name));
            }
        }
    }

    protected function getName(): string {
        return Package::Name;
    }
}
