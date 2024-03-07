# `assertScheduled`

Asserts that Schedule contains task.

[include:example]: ./AssertScheduled.php
[//]: # (start: 40742ef445ccdf2f6bd00219e5e620a0f2e2972627292c6f7818883465dca83e)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleAssertions;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
final class AssertScheduled extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use ScheduleAssertions;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        // Prepare
        /** @var Schedule $schedule */
        $schedule = Container::getInstance()->make(Schedule::class);
        $schedule
            ->command('emails:send Example')
            ->daily();
        $schedule
            ->exec('/path/to/command')
            ->daily();

        // Test
        self::assertScheduled('emails:send Example');
        self::assertScheduled('/path/to/command');
    }
}
```

Example output:

```plain
OK (1 test, 2 assertions)
```

[//]: # (end: 40742ef445ccdf2f6bd00219e5e620a0f2e2972627292c6f7818883465dca83e)
