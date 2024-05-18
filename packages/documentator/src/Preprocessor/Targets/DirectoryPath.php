<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirectory;
use Override;

use function dirname;
use function is_dir;

/**
 * Directory path.
 *
 * @implements Resolver<null, string>
 */
class DirectoryPath implements Resolver {
    public function __construct() {
        // empty
    }

    #[Override]
    public function resolve(Context $context, mixed $parameters): string {
        $path = Path::getPath(dirname($context->path), $context->target);

        if (!is_dir($path)) {
            throw new TargetIsNotDirectory($context);
        }

        return $path;
    }
}