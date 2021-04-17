<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithTranslations;
use LastDragon_ru\LaraASP\GraphQL\Helpers\EnumHelper;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByDirective;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
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
    }

    public function register(): void {
        parent::register();

        $this->registerDirectives();
        $this->registerEnums();
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
        $this->app->bind(SearchByDirective::class, function (): SearchByDirective {
            $container  = $this->app;
            $translator = $container->make(PackageTranslator::class);
            $directives = $container->make(DirectiveLocator::class);
            $config     = $container->make(Repository::class);
            $scalars    = $config->get("{$this->getName()}.search_by.scalars");
            $instance   = new SearchByDirective($container, $translator, $directives, $scalars);

            return $instance;
        });
    }

    protected function registerEnums(): void {
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
