<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instruction;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;

use function dirname;
use function file_get_contents;

class IncludeFile implements Instruction {
    public function __construct() {
        // empty
    }

    public static function getName(): string {
        return 'include:file';
    }

    public function process(string $path, string $target): string {
        $file    = Path::getPath(dirname($path), $target);
        $content = file_get_contents($file);

        if ($content === false) {
            throw new TargetIsNotFile($path, $target);
        }

        return $content;
    }
}
