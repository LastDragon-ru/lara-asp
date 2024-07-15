<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Resolvers;

use Generator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

/**
 * File path.
 *
 * @implements Resolver<File, null>
 */
class FileResolver implements Resolver {
    /**
     * @return Generator<mixed, Dependency<*>, mixed, File>
     */
    #[Override]
    public function __invoke(Context $context, mixed $parameters): Generator {
        return Cast::to(File::class, yield new FileReference($context->target));
    }
}
