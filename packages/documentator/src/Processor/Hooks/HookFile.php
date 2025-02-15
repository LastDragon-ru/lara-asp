<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Hooks;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

use function array_find;

/**
 * @internal
 */
class HookFile extends File {
    public function __construct(
        FilePath $path,
        /**
         * @var array<array-key, object>
         */
        protected array $metadata = [],
    ) {
        parent::__construct($path);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $metadata
     *
     * @return T
     */
    #[Override]
    public function as(string $metadata): object {
        $value = array_find($this->metadata, static function (object $value) use ($metadata): bool {
            return $value instanceof $metadata;
        });

        if (!($value instanceof $metadata)) {
            throw new MetadataUnresolvable($this, $metadata);
        }

        return $value;
    }
}
