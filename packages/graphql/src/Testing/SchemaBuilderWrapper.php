<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Schema;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as EventsDispatcher;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Schema\AST\ASTBuilder;
use Nuwave\Lighthouse\Schema\AST\ASTCache;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function spl_object_hash;

class SchemaBuilderWrapper extends SchemaBuilder {
    protected ?SchemaBuilder $current = null;

    /**
     * @var array<class-string, object>
     */
    protected array $singletons = [];

    /**
     * @noinspection             PhpMissingParentConstructorInspection
     * @phpstan-ignore-next-line no need to call parent `__construct`
     */
    public function __construct(
        protected Container $container,
        protected SchemaBuilder $builder,
    ) {
        // no need to call parent
    }

    protected function getSchemaBuilder(): SchemaBuilder {
        return $this->current ?? $this->builder;
    }

    public function schema(): Schema {
        return $this->getSchemaBuilder()->schema();
    }

    public function setSchema(?SchemaSourceProvider $provider): void {
        // Origins
        if (!$this->singletons) {
            $this->singletons = [
                ASTCache::class   => $this->container->make(ASTCache::class),
                ASTBuilder::class => $this->container->make(ASTBuilder::class),
            ];
        }

        // Build
        $builder = null;

        if ($provider) {
            $config = $this->container->make(ConfigContract::class)->all();

            Arr::set($config, 'lighthouse.cache.key', spl_object_hash($provider));
            Arr::set($config, 'lighthouse.cache.enable', true);
            Arr::set($config, 'lighthouse.cache.version', 1); // cache

            $types      = $this->container->make(TypeRegistry::class);
            $dispatcher = $this->container->make(EventsDispatcher::class);
            $directives = $this->container->make(DirectiveLocator::class);
            $astCache   = new ASTCache(new Repository($config));
            $astBuilder = new ASTBuilder($directives, $provider, $dispatcher, $astCache);
            $builder    = new SchemaBuilder($types, $astBuilder);

            $this->container->instance(ASTCache::class, $astCache);
            $this->container->instance(ASTBuilder::class, $astBuilder);
        } else {
            foreach ($this->singletons as $abstract => $instance) {
                $this->container->instance($abstract, $instance);
            }
        }

        // Set
        $this->current = $builder;
    }
}
