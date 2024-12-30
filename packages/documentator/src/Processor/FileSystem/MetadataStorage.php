<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileMetadataUnresolvable;
use Override;
use WeakMap;

use function array_key_exists;

/**
 * @internal
 */
class MetadataStorage implements MetadataResolver {
    /**
     * @var WeakMap<File, array<class-string<Metadata<mixed>>, mixed>>
     */
    private WeakMap $storage;

    /**
     * @var array<class-string<Metadata<mixed>>, Metadata<mixed>>
     */
    private array $instances = [];

    public function __construct(
        protected readonly ContainerResolver $container,
    ) {
        $this->storage = new WeakMap();
    }

    /**
     * @template T
     *
     * @param class-string<Metadata<T>> $metadata
     *
     * @return T
     */
    #[Override]
    public function get(File $file, string $metadata): mixed {
        if (!isset($this->storage[$file])) {
            $this->storage[$file] = [];
        }

        if (!array_key_exists($metadata, $this->storage[$file])) {
            try {
                $this->storage[$file][$metadata] = ($this->resolve($metadata))($file);
            } catch (Exception $exception) {
                throw new FileMetadataUnresolvable($file, $metadata, $exception);
            }
        }

        return $this->storage[$file][$metadata];
    }

    /**
     * @template T
     *
     * @param class-string<Metadata<T>> $metadata
     */
    public function has(File $file, string $metadata): bool {
        return array_key_exists($metadata, $this->storage[$file] ?? []);
    }

    /**
     * @template T
     *
     * @param class-string<Metadata<T>> $metadata
     * @param T                         $value
     */
    public function set(File $file, string $metadata, mixed $value): void {
        $this->storage[$file]          ??= [];
        $this->storage[$file][$metadata] = $value;
    }

    public function reset(File $file): void {
        $this->storage[$file] = [];
    }

    /**
     * @template T
     *
     * @param class-string<Metadata<T>> $metadata
     *
     * @return Metadata<T>
     */
    protected function resolve(string $metadata): Metadata {
        if (!isset($this->instances[$metadata])) {
            $this->instances[$metadata] = $this->container->getInstance()->make($metadata);
        }

        return $this->instances[$metadata]; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }
}
