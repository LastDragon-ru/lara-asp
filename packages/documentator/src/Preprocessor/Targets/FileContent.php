<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use Override;

use function file_get_contents;

/**
 * File path.
 */
class FileContent extends FilePath {
    /**
     * @inheritDoc
     */
    #[Override]
    public function resolve(Context $context, mixed $parameters, array $dependencies): string {
        $path    = parent::resolve($context, $parameters, $dependencies);
        $content = file_get_contents($path);

        if ($content === false) {
            throw new TargetIsNotFile($context);
        }

        return $content;
    }
}
