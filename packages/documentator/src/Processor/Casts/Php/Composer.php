<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Composer\ComposerJson;
use LastDragon_ru\LaraASP\Documentator\Composer\Package;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Serialized;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use Override;

/**
 * @implements Cast<Package>
 */
readonly class Composer implements Cast {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(Resolver $resolver, File $file): Package {
        $json    = $resolver->cast($file, Serialized::class)->to(ComposerJson::class);
        $package = new Package($json);

        return $package;
    }
}
