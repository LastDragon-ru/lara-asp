<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator;

use LastDragon_ru\LaraASP\Core\Helpers\Translator;
use Override;

class PackageTranslator extends Translator {
    #[Override]
    protected function getName(): string {
        return Package::Name;
    }
}
