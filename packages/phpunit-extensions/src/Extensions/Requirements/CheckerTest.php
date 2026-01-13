<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\Requirements;

use Attribute;
use LastDragon_ru\PhpUnit\Extensions\Requirements\Contracts\Requirement;
use LastDragon_ru\PhpUnit\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal
 */
#[CoversClass(Checker::class)]
final class CheckerTest extends TestCase {
    public function testIsSatisfied(): void {
        $checker = new Checker();
        $failed  = [];

        self::assertFalse($checker->isSatisfied(CheckerTest_ClassNotMeetsRequirements::class, null, $failed));
        self::assertEquals(['class'], $failed);
        self::assertFalse(
            $checker->isSatisfied(CheckerTest_ClassNotMeetsRequirements::class, 'methodMeetsRequirements', $failed),
        );
        self::assertEquals(['class'], $failed);
        self::assertFalse(
            $checker->isSatisfied(CheckerTest_ClassNotMeetsRequirements::class, 'methodNotMeetsRequirements', $failed),
        );
        self::assertEquals(['class', 'method'], $failed);

        self::assertTrue($checker->isSatisfied(CheckerTest_ClassMeetsRequirements::class, null, $failed));
        self::assertEquals([], $failed);
        self::assertTrue(
            $checker->isSatisfied(CheckerTest_ClassMeetsRequirements::class, 'methodMeetsRequirements', $failed),
        );
        self::assertEquals([], $failed);
        self::assertFalse(
            $checker->isSatisfied(CheckerTest_ClassMeetsRequirements::class, 'methodNotMeetsRequirements', $failed),
        );
        self::assertEquals(['method'], $failed);
    }

    public function testIsSatisfiedCache(): void {
        $checker = Mockery::mock(Checker::class);
        $checker->shouldAllowMockingProtectedMethods();
        $checker->makePartial();
        $checker
            ->shouldReceive('getFailedRequirements')
            ->with(Mockery::type(ReflectionClass::class))
            ->once()
            ->andReturn(['class']);
        $checker
            ->shouldReceive('getFailedRequirements')
            ->with(Mockery::type(ReflectionMethod::class))
            ->once()
            ->andReturn(['method']);

        self::assertFalse($checker->isSatisfied(CheckerTest_ClassNotMeetsRequirements::class));
        self::assertFalse($checker->isSatisfied(CheckerTest_ClassNotMeetsRequirements::class));
        self::assertFalse(
            $checker->isSatisfied(CheckerTest_ClassNotMeetsRequirements::class, 'methodMeetsRequirements'),
        );
        self::assertFalse(
            $checker->isSatisfied(CheckerTest_ClassNotMeetsRequirements::class, 'methodMeetsRequirements'),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
readonly class CheckerTest_Requirement implements Requirement {
    public function __construct(
        private string $reason,
        private bool $satisfied,
    ) {
        // empty
    }

    #[Override]
    public function isSatisfied(): bool {
        return $this->satisfied;
    }

    #[Override]
    public function __toString(): string {
        return $this->reason;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[CheckerTest_Requirement('class', false)]
class CheckerTest_ClassNotMeetsRequirements {
    #[CheckerTest_Requirement('method', true)]
    public function methodMeetsRequirements(): bool {
        return false;
    }

    #[CheckerTest_Requirement('method', false)]
    public function methodNotMeetsRequirements(): bool {
        return false;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[CheckerTest_Requirement('class', true)]
class CheckerTest_ClassMeetsRequirements {
    #[CheckerTest_Requirement('method', true)]
    public function methodMeetsRequirements(): bool {
        return false;
    }

    #[CheckerTest_Requirement('method', false)]
    public function methodNotMeetsRequirements(): bool {
        return false;
    }
}
