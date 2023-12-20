<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Assertions\Application\CronableAssertions;
use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleAssertions;

trait Assertions {
    use XmlAssertions;
    use JsonAssertions;
    use ScoutAssertions;
    use ScheduleAssertions;
    use CronableAssertions;
    use ResponseAssertions;
    use DatabaseAssertions;
}
