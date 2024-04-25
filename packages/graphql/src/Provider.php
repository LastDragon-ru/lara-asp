<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Provider\WithConfig;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver as BuilderFieldResolverContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Defaults\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\ManipulatorFactory;
use LastDragon_ru\LaraASP\GraphQL\Directives\Definitions\TypeDirective;
use LastDragon_ru\LaraASP\GraphQL\Printer\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchBySchemaDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\SorterFactory as SorterFactoryContract;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortBySchemaDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\SorterFactory;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\StreamFactory as StreamFactoryContract;
use LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\StreamFactory;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver as DirectiveResolverContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as SchemaPrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings as SettingsContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use Nuwave\Lighthouse\Events\BuildSchemaString;
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
        $this->registerSchemaPrinter();
    }

    protected function bootDirectives(Dispatcher $dispatcher): void {
        $dispatcher->listen(
            RegisterDirectiveNamespaces::class,
            static function (): array {
                return [
                    implode('\\', array_slice(explode('\\', SearchByDirective::class), 0, -1)),
                    implode('\\', array_slice(explode('\\', SortByDirective::class), 0, -1)),
                    implode('\\', array_slice(explode('\\', StreamDirective::class), 0, -1)),
                    implode('\\', array_slice(explode('\\', TypeDirective::class), 0, -1)),
                ];
            },
        );
        $dispatcher->listen(BuildSchemaString::class, SearchBySchemaDirective::class);
        $dispatcher->listen(BuildSchemaString::class, SortBySchemaDirective::class);
    }

    protected function registerBindings(): void {
        $this->app->scopedIf(ManipulatorFactory::class);
        $this->app->scopedIf(SorterFactoryContract::class, SorterFactory::class);
        $this->app->scopedIf(StreamFactoryContract::class, StreamFactory::class);
        $this->app->scopedIf(BuilderFieldResolverContract::class, BuilderFieldResolver::class);
    }

    protected function registerSchemaPrinter(): void {
        $this->app->scopedIf(SettingsContract::class, DefaultSettings::class);
        $this->app->scopedIf(DirectiveResolverContract::class, DirectiveResolver::class);
        $this->app->scopedIf(
            SchemaPrinterContract::class,
            static function (Container $container): SchemaPrinterContract {
                $settings = $container->make(SettingsContract::class);
                $resolver = $container->make(DirectiveResolverContract::class);
                $schema   = $container->make(SchemaBuilder::class)->schema();
                $printer  = new Printer($settings, $resolver, $schema);

                return $printer;
            },
        );
    }

    #[Override]
    protected function getName(): string {
        return Package::Name;
    }
}
