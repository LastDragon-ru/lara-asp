<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Observer;

use Closure;
use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;
use Mockery;
use stdClass;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Core\Observer\Subject
 */
class SubjectTest extends TestCase {
    /**
     * @covers ::attach
     * @covers ::detach
     * @covers ::notify
     */
    public function testSubject(): void {
        $spy      = Mockery::spy(static fn(stdClass $context) => null);
        $context  = new stdClass();
        $observer = Closure::fromCallable($spy);
        $subject  = new Subject();

        $subject->attach($observer);
        $subject->attach($observer);

        $subject->notify($context);

        $subject->detach($observer);

        $subject->notify($context);

        $spy
            ->shouldHaveBeenCalled()
            ->once()
            ->with($context);
    }

    /**
     * @covers ::reset
     */
    public function testReset(): void {
        $spy      = Mockery::spy(static fn(stdClass $context) => null);
        $context  = new stdClass();
        $observer = Closure::fromCallable($spy);
        $subject  = new Subject();

        $subject->attach($observer);

        $subject->reset();

        $subject->notify($context);

        $spy->shouldNotHaveBeenCalled();
    }
}
