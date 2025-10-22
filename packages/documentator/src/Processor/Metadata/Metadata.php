<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnserializable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
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

use function class_exists;
use function class_implements;
use function sprintf;

class Metadata {
    /**
     * @var WeakMap<File, array<class-string<object>, object>>
     */
    private WeakMap                  $files;
    private readonly Resolvers       $resolvers;
    private readonly ContentMetadata $content;

    public function __construct(
        protected readonly ContainerResolver $container,
        protected readonly FileSystemAdapter $adapter,
    ) {
        $this->files     = new WeakMap();
        $this->content   = new ContentMetadata($this->adapter);
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
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $metadata
     *
     * @return T
     */
    public function get(File $file, string $metadata): mixed {
        if (!isset($this->files[$file][$metadata])) {
            try {
                $resolver = $this->getResolver($file, $metadata);
                $resolved = $resolver->resolve($file, $metadata);

                if ($resolved instanceof $metadata) {
                    $this->files[$file]          ??= [];
                    $this->files[$file][$metadata] = $resolved;
                } else {
                    throw new UnexpectedValueException(
                        sprintf(
                            'Expected `%s`, got `%s` (resolver `%s`).',
                            $metadata,
                            ($resolved !== null ? $resolved::class : 'null'),
                            $resolver::class,
                        ),
                    );
                }
            } catch (Exception $exception) {
                throw new MetadataUnresolvable($file, $metadata, $exception);
            }
        }

        return $this->files[$file][$metadata]; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }

    /**
     * @param class-string $metadata
     */
    public function has(File $file, string $metadata): bool {
        return isset($this->files[$file][$metadata]);
    }

    /**
     * @template T of object
     *
     * @param T $metadata
     */
    public function set(File $file, object $metadata): void {
        try {
            $resolver                                  = $this->getResolver($file, $metadata::class);
            $this->files[$file]                      ??= [];
            $this->files[$file][$resolver::getClass()] = $metadata;
        } catch (Exception $exception) {
            throw new MetadataUnresolvable($file, $metadata::class, $exception);
        }
    }

    public function reset(File $file): void {
        $this->files[$file] = [];
    }

    public function serialize(File $file, object $value): string {
        try {
            $resolver   = $this->getResolver($file, $value::class);
            $serialized = $resolver->serialize($file, $value);

            if ($serialized === null) {
                throw new RuntimeException('Serializer not found.');
            }
        } catch (Exception $exception) {
            throw new MetadataUnserializable($file, $value, $exception);
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
        $tags       = [];
        $class      = $metadata::getClass();
        $extensions = $metadata::getExtensions();

        foreach ($extensions as $extension) {
            $tags[] = "{$class}:{$extension}";
        }

        $this->resolvers->add($metadata, $tags, $priority);
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
    protected function getResolver(File $file, string $metadata): MetadataResolver {
        $resolver = match (true) {
            $metadata === Content::class => $this->content,
            default                      => $this->resolvers->first(...$this->getTags($file, $metadata)),
        };

        if (!($resolver instanceof MetadataResolver)) {
            throw new RuntimeException('Resolver not found.');
        }

        return $resolver; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }

    /**
     * @param class-string $metadata
     *
     * @return list<string>
     */
    protected function getTags(File $file, string $metadata): array {
        $tags       = [];
        $extensions = [$file->getExtension(), '*'];
        $implements = [$metadata, ...(class_exists($metadata) ? (array) class_implements($metadata) : [])];

        foreach ($implements as $interface) {
            foreach ($extensions as $extension) {
                $tags[] = "{$interface}:{$extension}";
            }
        }

        return $tags;
    }
}
