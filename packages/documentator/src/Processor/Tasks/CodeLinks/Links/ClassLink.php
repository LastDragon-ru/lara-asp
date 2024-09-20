<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use Override;

class ClassLink extends Base implements Link {
    #[Override]
    public function __toString(): string {
        return $this->class;
    }
}
