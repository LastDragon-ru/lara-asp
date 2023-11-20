<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use Override;

use function dirname;
use function file_get_contents;
use function rtrim;

class IncludeFile implements ProcessableInstruction {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:file';
    }

    #[Override]
    public static function getDescription(): string {
        return 'Includes the `<target>` file.';
    }

    #[Override]
    public static function getTargetDescription(): ?string {
        return 'File path.';
    }

    #[Override]
    public function process(string $path, string $target): string {
        // Content
        $file    = Path::getPath(dirname($path), $target);
        $content = file_get_contents($file);

        if ($content === false) {
            throw new TargetIsNotFile($path, $target);
        }

        // Return
        return rtrim($content)."\n";
    }
}
