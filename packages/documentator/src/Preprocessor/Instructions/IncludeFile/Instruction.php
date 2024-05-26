<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Resolvers\FileResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

use function rtrim;

/**
 * Includes the `<target>` file.
 *
 * @implements InstructionContract<File, null>
 */
class Instruction implements InstructionContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:file';
    }

    #[Override]
    public static function getResolver(): string {
        return FileResolver::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return null;
    }

    #[Override]
    public function __invoke(Context $context, mixed $target, mixed $parameters): string {
        return rtrim($target->getContent())."\n";
    }
}
