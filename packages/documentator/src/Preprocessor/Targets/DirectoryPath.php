<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\TargetResolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirectory;
use Override;

use function dirname;
use function is_dir;

/**
 * Directory path.
 *
 * @template TParameters
 *
 * @implements TargetResolver<TParameters, string>
 */
class DirectoryPath implements TargetResolver {
    public function __construct() {
        // empty
    }

    #[Override]
    public function resolve(Context $context, mixed $parameters): string {
        $path = Path::getPath(dirname($context->path), $context->target);

        if (!is_dir($path)) {
            throw new TargetIsNotDirectory($context->path, $context->target);
        }

        return $path;
    }
}
