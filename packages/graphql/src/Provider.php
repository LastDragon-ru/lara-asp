<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithTranslations;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver as ScoutFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Scout\DefaultFieldResolver as ScoutDefaultFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\SchemaPrinter;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators as SearchByOperators;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators as SortByOperators;
use LastDragon_ru\LaraASP\GraphQL\Utils\Enum\EnumType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as SchemaPrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings as SettingsContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_slice;
use function config;
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
        $this->registerDirectives();
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

    protected function registerDirectives(): void {
        $this->app->bindIf(ScoutFieldResolver::class, ScoutDefaultFieldResolver::class);
        $this->callAfterResolving(
            Manipulator::class,
            static function (Manipulator $manipulator): void {
                $manipulator->addOperators(new SearchByOperators());
                $manipulator->addOperators(new SortByOperators());
            },
        );
    }

    protected function registerEnums(): void {
        $this->callAfterResolving(
            TypeRegistry::class,
            function (TypeRegistry $registry): void {
                $enums = (array) config("{$this->getName()}.enums");

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
