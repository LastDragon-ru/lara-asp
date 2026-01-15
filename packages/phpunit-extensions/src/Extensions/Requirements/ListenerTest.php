<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\Requirements;

use LastDragon_ru\PhpUnit\Package\TestCase;
use Mockery;
use PHPUnit\Event\Event;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\SkippedWithMessageException;

/**
 * @internal
 */
#[CoversClass(Listener::class)]
final class ListenerTest extends TestCase {
    public function testNotify(): void {
        self::expectException(SkippedWithMessageException::class); // @phpstan-ignore classConstant.internalClass
        self::expectExceptionMessage('Unknown requirement.');

        $event   = Mockery::mock(Event::class);
        $checker = Mockery::mock(Checker::class);
        $checker
            ->shouldReceive('isSatisfied')
            ->with(static::class, null, [])
            ->once()
            ->andReturn(false);
        $listener = Mockery::mock(Listener::class, [$checker]);
        $listener->shouldAllowMockingProtectedMethods();
        $listener->makePartial();
        $listener
            ->shouldReceive('getTarget')
            ->with($event)
            ->once()
            ->andReturn([static::class, null]);

        $listener->notify($event);
    }
}
