<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Resolvers;

use Generator;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;
use SplFileInfo;

/**
 * File path.
 *
 * @implements Resolver<null, File>
 */
class FileResolver implements Resolver {
    /**
     * @return Generator<mixed, SplFileInfo|File|string, File, File>
     */
    #[Override]
    public function __invoke(Context $context, mixed $parameters): Generator {
        return yield $context->target;
    }
}
