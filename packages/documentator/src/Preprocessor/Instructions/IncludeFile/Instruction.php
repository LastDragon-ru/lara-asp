<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeFile;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets\FileContent;
use Override;

use function rtrim;

/**
 * Includes the `<target>` file.
 *
 * @implements InstructionContract<string, null>
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
    public static function getTarget(): string {
        return FileContent::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return null;
    }

    #[Override]
    public function process(Context $context, mixed $target, mixed $parameters): string {
        return rtrim($target)."\n";
    }
}
