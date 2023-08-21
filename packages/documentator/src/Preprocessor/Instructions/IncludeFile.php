<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PreprocessFailed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Instruction;
use Symfony\Component\Filesystem\Filesystem;

use function file_get_contents;
use function sprintf;

class IncludeFile implements Instruction {
    public function __construct() {
        // empty
    }

    public static function getName(): string {
        return 'include:file';
    }

    public function process(string $path, string $target): string {
        $file    = (new Filesystem())->isAbsolutePath($target) ? $target : "{$path}/{$target}";
        $content = file_get_contents($file);

        if ($content === false) {
            throw new PreprocessFailed(
                sprintf(
                    'Failed to include `%s`.',
                    $target,
                ),
            );
        }

        return $content;
    }
}
