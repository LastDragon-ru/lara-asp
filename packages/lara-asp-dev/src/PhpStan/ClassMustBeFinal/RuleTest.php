<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\PhpStan\ClassMustBeFinal;

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
        return new Rule([
            RuleTest_MustBeFinalMarker::class,
        ]);
    }

    public function testRule(): void {
        $this->analyse([__FILE__], [
            [
                sprintf('Class `%s` must be `final`.', RuleTest_MustBeFinal::class),
                52,
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
interface RuleTest_MustBeFinalMarker {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class RuleTest_MustBeFinal implements RuleTest_MustBeFinalMarker {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
final class RuleTest_AlreadyFinal implements RuleTest_MustBeFinalMarker {
    // empty
}
