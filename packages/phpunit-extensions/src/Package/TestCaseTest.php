<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Package;

use Exception;
use LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare\Extension;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\ExpectationFailedException;

use function sprintf;

/**
 * @internal
 */
#[CoversNothing]
final class TestCaseTest extends TestCase {
    public function testIntegration(): void {
        $exception = null;

        try {
            self::assertEquals(1, true);
        } catch (Exception $exception) {
            // empty
        }

        self::assertInstanceOf(
            ExpectationFailedException::class,
            $exception,
            sprintf(
                'Extension `%s` is not registered?',
                Extension::class,
            ),
        );
    }
}
