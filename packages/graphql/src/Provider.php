<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithTranslations;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver as ScoutFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Scout\DefaultFieldResolver as ScoutDefaultFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\SchemaPrinter as SchemaPrinterContract;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings as SettingsContract;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\SchemaPrinter;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators as SearchByOperators;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByDirective;
use LastDragon_ru\LaraASP\GraphQL\Utils\Enum\EnumType;
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
        $this->bootDirectives($dispatcher);
    }

    public function register(): void {
        parent::register();

        $this->registerEnums();
        $this->registerBuilders();
        $this->registerSchemaPrinter();
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

    protected function registerBuilders(): void {
        $this->app->singleton(ScoutFieldResolver::class, ScoutDefaultFieldResolver::class);
        $this->app->singleton(SearchByOperators::class);
    }

    protected function registerEnums(): void {
        $this->callAfterResolving(
            TypeRegistry::class,
            function (TypeRegistry $registry, Container $container): void {
                $enums = (array) $container->make(Repository::class)->get("{$this->getName()}.enums");

                foreach ($enums as $name => $enum) {
                    if (is_int($name)) {
                        $name = null;
                    }

                    $registry->register(new EnumType($enum, $name));
                }
            },
        );
    }

    protected function registerSchemaPrinter(): void {
        $this->app->bind(SettingsContract::class, DefaultSettings::class);
        $this->app->bind(SchemaPrinterContract::class, SchemaPrinter::class);
    }

    protected function getName(): string {
        return Package::Name;
    }
}
