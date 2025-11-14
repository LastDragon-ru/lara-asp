<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ExecutorState::class)]
final class ExecutorStateTest extends TestCase {
    public function testIs(): void {
        self::assertTrue(ExecutorState::Created->is(ExecutorState::Preparation, ExecutorState::Created));
        self::assertFalse(ExecutorState::Finished->is(ExecutorState::Created));
    }
}
