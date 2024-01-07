<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithConfig;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver as ScoutFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Scout\DefaultFieldResolver as ScoutDefaultFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Printer\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators as SearchByOperators;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\SorterFactory as SorterFactoryContract;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators as SortByOperators;
use LastDragon_ru\LaraASP\GraphQL\SortBy\SorterFactory;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\StreamFactory as StreamFactoryContract;
use LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\StreamFactory;
use LastDragon_ru\LaraASP\GraphQL\Utils\Definitions\LaraAspAsEnumDirective;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver as DirectiveResolverContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as SchemaPrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings as SettingsContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Override;

use function array_slice;
use function explode;
use function implode;

class Provider extends ServiceProvider {
    use WithConfig;

    public function boot(Dispatcher $dispatcher): void {
        $this->bootConfig();
        $this->bootDirectives($dispatcher);
    }

    #[Override]
    public function register(): void {
        parent::register();

        $this->registerBindings();
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
        $dispatcher->listen(
            RegisterDirectiveNamespaces::class,
            static function (): string {
                return implode('\\', array_slice(explode('\\', StreamDirective::class), 0, -1));
            },
        );
        $dispatcher->listen(
            RegisterDirectiveNamespaces::class,
            static function (): string {
                return implode('\\', array_slice(explode('\\', LaraAspAsEnumDirective::class), 0, -1));
            },
        );
    }

    protected function registerBindings(): void {
        $this->app->scopedIf(SorterFactoryContract::class, SorterFactory::class);
        $this->app->bindIf(StreamFactoryContract::class, StreamFactory::class);
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

    protected function registerSchemaPrinter(): void {
        $this->app->bindIf(SettingsContract::class, DefaultSettings::class);
        $this->app->bindIf(DirectiveResolverContract::class, DirectiveResolver::class);
        $this->app->bindIf(SchemaPrinterContract::class, static function (Container $container): SchemaPrinterContract {
            $settings = $container->make(SettingsContract::class);
            $resolver = $container->make(DirectiveResolverContract::class);
            $schema   = $container->make(SchemaBuilder::class)->schema();
            $printer  = new Printer($settings, $resolver, $schema);

            return $printer;
        });
    }

    #[Override]
    protected function getName(): string {
        return Package::Name;
    }
}
