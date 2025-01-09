<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\PhpStan\ClassConstantMustBeTyped;

use Override;
use PHPStan\Rules\Rule as RuleContract;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function sprintf;

/**
 * @internal
 * @extends RuleTestCase<Rule>
 */
#[CoversClass(Rule::class)]
final class RuleTest extends RuleTestCase {
    #[Override]
    protected function getRule(): RuleContract {
        return new Rule();
    }

    public function testRule(): void {
        $this->analyse([__FILE__], [
            [
                sprintf('Class constant `%s::%s` must be typed.', RuleTest_Constants::class, 'PublicUntyped'),
                49,
            ],
            [
                sprintf('Class constant `%s::%s` must be typed.', RuleTest_Constants::class, 'PrivateUntyped'),
                50,
            ],
            [
                sprintf('Class constant `%s::%s` must be typed.', RuleTest_Constants::class, 'ProtectedUntyped'),
                51,
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
class RuleTest_Constants {
    public const        PublicUntyped    = 123;
    private const       PrivateUntyped   = 123;
    protected const     ProtectedUntyped = 123;
    public const int    PublicTyped      = 123;
    private const int   PrivateTyped     = 123;
    protected const int ProtectedTyped   = 123;

    public function __construct() {
        echo self::PrivateTyped;
        echo self::PrivateUntyped;
    }
}
