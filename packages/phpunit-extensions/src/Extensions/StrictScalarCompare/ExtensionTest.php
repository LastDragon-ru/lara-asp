<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Extension::class)]
final class ExtensionTest extends TestCase {
    public function testIntegration(): void {
        $exception = null;

        try {
            self::assertEquals(1, true);
        } catch (Exception $exception) {
            // empty
        }

        self::assertInstanceOf(ExpectationFailedException::class, $exception);
        self::assertNotNull($exception->getComparisonFailure());
    }
}
