<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithTranslations;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Printer as PrinterContract;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings as SettingsContract;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Printer;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Metadata;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Repository as MetadataRepository;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Usage;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective;
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
        $this->registerSchemaPrinter();
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
            /** @var array<string,array<class-string<Operator>>|string> $scalars */
            $scalars  = (array) $app->make(Repository::class)->get("{$this->getName()}.search_by.scalars");
            $metadata = new Metadata($app, $app->make(Usage::class));

            foreach ($scalars as $scalar => $operators) {
                $metadata->addScalar($scalar, $operators);
            }

            return $metadata;
        });
    }

    protected function registerEnums(): void {
        $this->app->afterResolving(
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
        $this->app->bind(PrinterContract::class, Printer::class);
    }

    protected function getName(): string {
        return Package::Name;
    }
}
