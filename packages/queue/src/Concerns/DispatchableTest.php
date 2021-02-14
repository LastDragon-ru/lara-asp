<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use LastDragon_ru\LaraASP\Queue\Contracts\Initializable;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function sprintf;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Queue\Concerns\Dispatchable
 */
class DispatchableTest extends TestCase {
    /**
     * @covers ::dispatch
     */
    public function testDispatchUninitializedInitializable(): void {
        $job = new class() implements Initializable {
            use Dispatchable;
        };

        $this->expectExceptionObject(new RuntimeException(sprintf('The `%s` is not initialized.', $job::class)));

        $job->dispatch();
    }
}
