<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use Override;

use function file_get_contents;
use function is_string;

/**
 * File path.
 */
class FileContent extends FilePath {
    #[Override]
    public function __invoke(Context $context, mixed $parameters): mixed {
        $path    = parent::__invoke($context, $parameters);
        $content = is_string($path) ? file_get_contents($path) : false;

        if ($content === false) {
            throw new TargetIsNotFile($context);
        }

        return $content;
    }
}
