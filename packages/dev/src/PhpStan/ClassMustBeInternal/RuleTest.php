<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\PhpStan\ClassMustBeInternal;

use Override;
use PHPStan\Rules\Rule as RuleContract;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;

use function sprintf;

/**
 * @internal
 * @extends RuleTestCase<Rule>
 */
#[CoversClass(Rule::class)]
#[RunClassInSeparateProcess]
final class RuleTest extends RuleTestCase {
    #[Override]
    protected function getRule(): RuleContract {
        return new Rule(
            [
                RuleTest_MustBeInternalMarker::class,
            ],
            [
                RuleTest_MustBeIgnored::class,
            ],
        );
    }

    public function testRule(): void {
        $this->analyse([__FILE__], [
            [
                sprintf('Class `%s` must be marked by `@internal`.', RuleTest_MustBeInternal::class),
                60,
            ],
            [
                sprintf('Class `%s` must be marked by `@internal`.', RuleTest_TestMustBeInternal::class),
                82,
            ],
        ]);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
interface RuleTest_MustBeInternalMarker {
    // empty
}

/**
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class RuleTest_MustBeInternal implements RuleTest_MustBeInternalMarker {
    // empty
}

/**
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class RuleTest_MustBeIgnored implements RuleTest_MustBeInternalMarker {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class RuleTest_AlreadyInternal implements RuleTest_MustBeInternalMarker {
    // empty
}

/**
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class RuleTest_TestMustBeInternal {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class RuleTest_TestAlreadyInternal {
    // empty
}
