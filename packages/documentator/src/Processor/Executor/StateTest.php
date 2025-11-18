<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Executor;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(State::class)]
final class StateTest extends TestCase {
    public function testIs(): void {
        self::assertTrue(State::Created->is(State::Preparation, State::Created));
        self::assertFalse(State::Finished->is(State::Created));
    }
}
