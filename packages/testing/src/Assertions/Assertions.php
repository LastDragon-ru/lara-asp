<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Assertions\Application\CronableAssertions;

trait Assertions {
    use XmlAssertions;
    use JsonAssertions;
    use CronableAssertions;
    use StrictAssertEquals;
    use ResponseAssertions;
    use ModelComparator;
}
