<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use Override;

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
        $file = $context->directory->getFile($context->target);

        if (!$file) {
            throw new TargetIsNotFile($context);
        }

        return $file->getPath();
    }
}
