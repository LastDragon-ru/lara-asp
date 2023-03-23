<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Schema;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Events\Dispatcher as EventsDispatcher;
use Illuminate\Support\Arr;
use LastDragon_ru\LaraASP\Testing\Utils\WithTempDirectory;
use Nuwave\Lighthouse\Schema\AST\ASTBuilder;
use Nuwave\Lighthouse\Schema\AST\ASTCache;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function spl_object_hash;

class SchemaBuilderWrapper extends SchemaBuilder {
    use WithTempDirectory;

    protected ?SchemaBuilder $current = null;

    /**
     * @var array<class-string, object>
     */
    protected array $singletons = [];

    private string $directory;

    /**
     * @noinspection             PhpMissingParentConstructorInspection
     * @phpstan-ignore-next-line no need to call parent `__construct`
     */
    public function __construct(
        protected SchemaBuilder $builder,
    ) {
        // no need to call parent
    }

    protected function getDirectory(): string {
        return $this->directory ??= static::getTempDirectory();
    }

    protected function getSchemaBuilder(): SchemaBuilder {
        return $this->current ?? $this->builder;
    }

    public function schema(): Schema {
        return $this->getSchemaBuilder()->schema();
    }

    public function setSchema(?SchemaSourceProvider $provider): void {
        // Origins
        $container = Container::getInstance();

        if (!$this->singletons) {
            $this->singletons = [
                ASTCache::class   => $container->make(ASTCache::class),
                ASTBuilder::class => $container->make(ASTBuilder::class),
            ];
        }

        // Build
        $builder = null;

        if ($provider) {
            $config = $container->make(ConfigContract::class)->all();
            $path   = $this->getDirectory().'/'.spl_object_hash($provider).'.php';

            Arr::set($config, 'lighthouse.schema_cache.enable', true);
            Arr::set($config, 'lighthouse.schema_cache.path', $path);

            $types      = $container->make(TypeRegistry::class);
            $dispatcher = $container->make(EventsDispatcher::class);
            $directives = $container->make(DirectiveLocator::class);
            $astCache   = new ASTCache(new Repository($config));
            $astBuilder = new ASTBuilder($directives, $provider, $dispatcher, $astCache);
            $builder    = new SchemaBuilder($types, $astBuilder);

            $container->instance(ASTCache::class, $astCache);
            $container->instance(ASTBuilder::class, $astBuilder);
        } else {
            foreach ($this->singletons as $abstract => $instance) {
                $container->instance($abstract, $instance);
            }
        }

        // Set
        $this->current = $builder;
    }
}
