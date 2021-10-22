<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

trait Concerns {
    use StrictAssertEquals;
    use ModelComparator;
    use DatabaseQueryComparator;
    use Override;
}
