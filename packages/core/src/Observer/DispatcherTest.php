<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Observer;

use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Dispatcher::class)]
final class DispatcherTest extends TestCase {
    public function testSubject(): void {
        $spy      = Mockery::spy(static fn(stdClass $context) => null);
        $context  = new stdClass();
        $observer = $spy(...);
        $subject  = new Dispatcher();

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

    public function testReset(): void {
        $spy      = Mockery::spy(static fn(stdClass $context) => null);
        $context  = new stdClass();
        $observer = $spy(...);
        $subject  = new Dispatcher();

        $subject->attach($observer);

        $subject->reset();

        $subject->notify($context);

        $spy->shouldNotHaveBeenCalled();
    }

    public function testGetObservers(): void {
        $subject  = new Dispatcher();
        $observer = static function (): void {
            // empty
        };

        $subject->attach($observer);

        self::assertEquals([$observer], $subject->getObservers());
    }
}
