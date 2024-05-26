<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Resolvers;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirectory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use Override;

/**
 * Directory path.
 *
 * @implements Resolver<Directory, null>
 */
class DirectoryResolver implements Resolver {
    #[Override]
    public function __invoke(Context $context, mixed $parameters): Directory {
        $directory = $context->root->getDirectory($context->file)?->getDirectory($context->target);

        if (!($directory instanceof Directory)) {
            throw new TargetIsNotDirectory($context);
        }

        return $directory;
    }
}
