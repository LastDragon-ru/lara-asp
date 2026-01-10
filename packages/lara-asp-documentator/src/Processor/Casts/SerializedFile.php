<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;

class SerializedFile {
    /**
     * @var array<class-string<object>, object>
     */
    private array $cache = [];

    public function __construct(
        protected readonly Serializer $serializer,
        protected readonly File $file,
    ) {
        // empty
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function to(string $class): object {
        $this->cache[$class] ??= $this->serializer->deserialize($class, $this->file->content, $this->file->extension);

        return $this->cache[$class]; // @phpstan-ignore return.type (https://github.com/phpstan/phpstan/issues/9521)
    }

    public function toString(object $object): string {
        /**
         * The {@see self::to($class)} may differ from `$object::class`, this
         * is why we cache it only if matched.
         */
        if (isset($this->cache[$object::class])) {
            $this->cache[$object::class] = $object;
        }

        return $this->serializer->serialize($object, $this->file->extension);
    }
}
