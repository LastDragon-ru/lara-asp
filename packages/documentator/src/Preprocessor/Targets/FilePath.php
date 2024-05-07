<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use Override;

use function dirname;
use function is_file;

/**
 * File path.
 *
 * @implements Resolver<null, string>
 */
class FilePath implements Resolver {
    public function __construct() {
        // empty
    }

    #[Override]
    public function resolve(Context $context, mixed $parameters): string {
        $path = Path::getPath(dirname($context->path), $context->target);

        if (!is_file($path) || !is_readable($path)) {
            throw new TargetIsNotFile($context->path, $context->target);
        }

        return $path;
    }
}
