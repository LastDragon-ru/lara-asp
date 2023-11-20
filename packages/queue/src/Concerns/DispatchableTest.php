<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue\Concerns;

use LastDragon_ru\LaraASP\Queue\Contracts\Initializable;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(Dispatchable::class)]
class DispatchableTest extends TestCase {
    public function testDispatchUninitializedInitializable(): void {
        $job = new class() implements Initializable {
            use Dispatchable {
                isInitialized as traitIsInitialized;
            }

            #[Override]
            public function isInitialized(): bool {
                return $this->traitIsInitialized();
            }
        };

        self::expectExceptionObject(new RuntimeException(sprintf('The `%s` is not initialized.', $job::class)));

        $job->dispatch();
    }
    public function testRunUninitializedInitializable(): void {
        $job = new class() implements Initializable {
            use Dispatchable {
                isInitialized as traitIsInitialized;
            }

            #[Override]
            public function isInitialized(): bool {
                return $this->traitIsInitialized();
            }
        };

        self::expectExceptionObject(new RuntimeException(sprintf('The `%s` is not initialized.', $job::class)));

        $job->run();
    }
}
