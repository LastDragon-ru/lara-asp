<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithConfig;
use LastDragon_ru\LaraASP\Core\Concerns\ProviderWithTranslations;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\SearchByDirective;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;

use function array_slice;
use function explode;
use function implode;

class Provider extends ServiceProvider {
    use ProviderWithConfig;
    use ProviderWithTranslations;

    public function boot(Dispatcher $dispatcher): void {
        $this->bootConfig([
            'scalars',
        ]);
        $this->bootDirectives($dispatcher);
    }

    public function register(): void {
        parent::register();

        $this->registerDirectives();
    }

    protected function bootDirectives(Dispatcher $dispatcher): void {
        $dispatcher->listen(
            RegisterDirectiveNamespaces::class,
            static function (): string {
                return implode('\\', array_slice(explode('\\', SearchByDirective::class), 0, -1));
            },
        );
    }

    protected function registerDirectives(): void {
        $this->app->bind(SearchByDirective::class, function (): SearchByDirective {
            $container = $this->app;
            $config    = $container->make(Repository::class);
            $scalars   = $config->get("{$this->getName()}.search_by.scalars");
            $instance  = new SearchByDirective($container, $scalars);

            return $instance;
        });
    }

    protected function getName(): string {
        return Package::Name;
    }
}
