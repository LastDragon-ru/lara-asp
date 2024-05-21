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

    /**
     * @inheritDoc
     */
    #[Override]
    public function getDependencies(Context $context, mixed $parameters): array {
        return [];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function resolve(Context $context, mixed $parameters, array $dependencies): string {
        $file = $context->directory->getFile($context->target);

        if (!$file) {
            throw new TargetIsNotFile($context);
        }

        return $file->getPath();
    }
}
