<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;

use function hash;
use function uniqid;

/**
 * @internal
 */
class Utils {
    public static function getSeed(Context $context, File $file): string {
        $path = $file->getRelativePath($context->root) ?: uniqid(self::class); // @phpstan-ignore disallowed.function
        $path = hash('xxh3', $path);

        return $path;
    }
}
