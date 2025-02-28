<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Hook;
use OutOfBoundsException;
use Override;

use function array_find;
use function sprintf;

/**
 * @implements MetadataResolver<object>
 */
readonly class Context implements MetadataResolver {
    public function __construct(
        /**
         * @var array<array-key, object>
         */
        private array $context = [],
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return [Hook::Context->value];
    }

    #[Override]
    public function isSupported(string $metadata): bool {
        return $this->find($metadata) !== null;
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        $value = $this->find($metadata);

        if (!($value instanceof $metadata)) {
            throw new OutOfBoundsException(
                sprintf(
                    'The `%s` not found in `$context`.',
                    $metadata,
                ),
            );
        }

        return $value;
    }

    private function find(string $metadata): ?object {
        return array_find($this->context, static function (object $value) use ($metadata): bool {
            return $value instanceof $metadata;
        });
    }
}
