<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirectory;
use Override;

/**
 * Directory path.
 *
 * @implements Resolver<null, string>
 */
class DirectoryPath implements Resolver {
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
        $directory = $context->directory->getDirectory($context->target);

        if (!$directory) {
            throw new TargetIsNotDirectory($context);
        }

        return $directory->getPath();
    }
}
