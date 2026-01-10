<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator;

use LastDragon_ru\LaraASP\Core\Helpers\Viewer;
use Override;

class PackageViewer extends Viewer {
    #[Override]
    protected function getName(): string {
        return Package::Name;
    }
}
