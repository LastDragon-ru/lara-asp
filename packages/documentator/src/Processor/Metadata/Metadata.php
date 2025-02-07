<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileMetadataUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php\ClassMarkdownMetadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php\ComposerPackageMetadata;
use RuntimeException;
use UnexpectedValueException;
use WeakMap;

use function sprintf;

class Metadata {
    /**
     * @var WeakMap<File, array<class-string<object>, object>>
     */
    private WeakMap            $cache;
    private readonly Resolvers $resolvers;

    public function __construct(
        protected readonly ContainerResolver $container,
    ) {
        $this->cache     = new WeakMap();
        $this->resolvers = new Resolvers($container);

        $this->addResolver(ComposerPackageMetadata::class);
        $this->addResolver(ClassMarkdownMetadata::class);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $metadata
     *
     * @return T
     */
    public function get(File $file, string $metadata): mixed {
        // Resolved?
        if (isset($this->cache[$file][$metadata])) {
            return $this->cache[$file][$metadata]; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
        }

        // Resolve
        try {
            $resolver = $this->getResolver($file, $metadata);
            $resolved = $resolver->resolve($file, $metadata);

            if ($resolved instanceof $metadata) {
                $this->set($file, $resolved);
            } else {
                throw new UnexpectedValueException(sprintf(
                    'Expected `%s`, got `%s` (resolver `%s`).',
                    $metadata,
                    $resolved::class,
                    $resolver::class,
                ));
            }
        } catch (Exception $exception) {
            throw new FileMetadataUnresolvable($file, $metadata, $exception);
        }

        return $resolved;
    }

    /**
     * @param class-string $metadata
     */
    public function has(File $file, string $metadata): bool {
        return isset($this->cache[$file][$metadata]);
    }

    /**
     * @template T of object
     *
     * @param T $object
     */
    public function set(File $file, object $object): void {
        $this->cache[$file]               ??= [];
        $this->cache[$file][$object::class] = $object;
    }

    public function reset(File $file): void {
        $this->cache[$file] = [];
    }

    /**
     * @template V of object
     * @template R of MetadataResolver<V>
     *
     * @param R|class-string<R> $metadata
     */
    public function addResolver(MetadataResolver|string $metadata, ?int $priority = null): void {
        $this->resolvers->add($metadata, $priority);
    }

    /**
     * @template V of object
     * @template R of MetadataResolver<V>
     *
     * @param R|class-string<R> $metadata
     */
    public function removeResolver(MetadataResolver|string $metadata): void {
        $this->resolvers->remove($metadata);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $metadata
     *
     * @return MetadataResolver<T>
     */
    private function getResolver(File $file, string $metadata): MetadataResolver {
        $resolver  = null;
        $resolvers = $this->resolvers->get($file->getExtension(), '*');

        foreach ($resolvers as $instance) {
            if ($instance->isSupported($metadata)) {
                $resolver = $instance;
                break;
            }
        }

        if (!($resolver instanceof MetadataResolver)) {
            throw new RuntimeException(sprintf(
                'No resolver for metadata `%s`.',
                $metadata,
            ));
        }

        return $resolver; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }
}
