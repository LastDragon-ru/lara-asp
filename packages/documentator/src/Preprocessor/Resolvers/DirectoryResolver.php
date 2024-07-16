<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Resolvers;

use Generator;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotDirectory;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\DirectoryReference;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use Override;

/**
 * Directory path.
 *
 * @implements Resolver<Directory, null>
 */
class DirectoryResolver implements Resolver {
    /**
     * @return Generator<mixed, Dependency<*>, mixed, Directory>
     */
    #[Override]
    public function __invoke(Context $context, mixed $parameters): Generator {
        $directory = yield new DirectoryReference($context->target);

        if (!($directory instanceof Directory)) {
            throw new TargetIsNotDirectory($context);
        }

        return $directory;
    }
}
