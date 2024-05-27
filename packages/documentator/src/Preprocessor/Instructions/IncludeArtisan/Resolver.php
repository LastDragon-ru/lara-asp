<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeArtisan;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver as ResolverContract;
use Override;

use function dirname;
use function strtr;

/**
 * Artisan command. The following special variables supported:
 *
 * * `"{$directory}"` - path of the directory where the file is located.
 * * `"{$file}"` - path of the file.
 *
 * @implements ResolverContract<string, null>
 */
class Resolver implements ResolverContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(Context $context, mixed $parameters): mixed {
        $file      = $context->file->getPath();
        $directory = dirname($file);
        $target    = strtr($context->target, [
            '"{$directory}"' => "\"{$directory}\"",
            '"{$file}"'      => "\"{$file}\"",
        ]);

        return $target;
    }
}
