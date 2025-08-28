<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnserializable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\ContentMetadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Markdown\MarkdownMetadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php\ClassCommentMetadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php\ClassMarkdownMetadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php\ClassObjectMetadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php\ComposerPackageMetadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Serializer\SerializableMetadata;
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

        $this->addBuiltInResolvers();
    }

    protected function addBuiltInResolvers(): void {
        $this->addResolver(ComposerPackageMetadata::class);
        $this->addResolver(ClassMarkdownMetadata::class);
        $this->addResolver(ClassCommentMetadata::class);
        $this->addResolver(ClassObjectMetadata::class);
        $this->addResolver(SerializableMetadata::class);
        $this->addResolver(MarkdownMetadata::class);
        $this->addResolver(ContentMetadata::class);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $metadata
     *
     * @return T
     */
    public function get(File $file, string $metadata): mixed {
        try {
            $resolver = $this->getResolver($file, $metadata);
            $resolved = $this->cache[$file][$resolver::class] ?? null;

            if ($resolved === null) {
                $resolved = $resolver->resolve($file, $metadata);

                if ($resolved instanceof $metadata) {
                    $this->set($file, $resolved);
                } else {
                    throw new UnexpectedValueException(
                        sprintf(
                            'Expected `%s`, got `%s` (resolver `%s`).',
                            $metadata,
                            $resolved::class,
                            $resolver::class,
                        ),
                    );
                }
            }
        } catch (Exception $exception) {
            throw new MetadataUnresolvable($file, $metadata, $exception);
        }

        return $resolved; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }

    /**
     * @param class-string $metadata
     */
    public function has(File $file, string $metadata): bool {
        try {
            $resolver = $this->getResolver($file, $metadata);
            $exists   = isset($this->cache[$file][$resolver::class]);

            return $exists;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @template T of object
     *
     * @param T $metadata
     */
    public function set(File $file, object $metadata): void {
        try {
            $resolver                             = $this->getResolver($file, $metadata::class);
            $this->cache[$file]                 ??= [];
            $this->cache[$file][$resolver::class] = $metadata;
        } catch (Exception $exception) {
            throw new MetadataUnresolvable($file, $metadata::class, $exception);
        }
    }

    public function reset(File $file): void {
        $this->cache[$file] = [];
    }

    public function serialize(FilePath $path, object $value): string {
        try {
            $resolver   = $this->getResolver($path, $value::class);
            $serialized = $resolver->serialize($path, $value);

            if ($serialized === null) {
                throw new RuntimeException('Serializer not found.');
            }
        } catch (Exception $exception) {
            throw new MetadataUnserializable($path, $value, $exception);
        }

        return $serialized;
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
    private function getResolver(File|FilePath $path, string $metadata): MetadataResolver {
        $resolver  = null;
        $resolvers = $this->resolvers->get($path->getExtension(), '*');

        foreach ($resolvers as $instance) {
            if ($instance->isSupported($metadata)) {
                $resolver = $instance;
                break;
            }
        }

        if (!($resolver instanceof MetadataResolver)) {
            throw new RuntimeException('Resolver not found.');
        }

        return $resolver; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }
}
