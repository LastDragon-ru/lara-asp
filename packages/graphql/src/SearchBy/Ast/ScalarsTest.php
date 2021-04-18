<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNotNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Scalars
 */
class ScalarsTest extends TestCase {
    /**
     * @covers ::isScalar
     */
    public function testIsScalar(): void {
        $this->assertTrue((new Scalars())->isScalar(Directive::ScalarInt));
        $this->assertFalse((new Scalars())->isScalar('unknown'));
    }

    /**
     * @covers ::getScalarOperators
     */
    public function testGetScalarOperators(): void {
        $scalar  = __FUNCTION__;
        $alias   = 'alias';
        $scalars = new Scalars([
            $scalar => [Equal::class, Equal::class],
            $alias  => $scalar,
        ]);

        $this->assertEquals([], $scalars->getScalarOperators('unknown', false));
        $this->assertEquals([IsNull::class, IsNotNull::class], $scalars->getScalarOperators('unknown', true));
        $this->assertEquals([Equal::class], $scalars->getScalarOperators($scalar, false));
        $this->assertEquals(
            [Equal::class, IsNull::class, IsNotNull::class],
            $scalars->getScalarOperators($scalar, true),
        );
        $this->assertEquals(
            $scalars->getScalarOperators($scalar, false),
            $scalars->getScalarOperators($alias, false),
        );
        $this->assertEquals(
            $scalars->getScalarOperators($scalar, true),
            $scalars->getScalarOperators($alias, true),
        );
    }

    /**
     * @covers ::getEnumOperators
     */
    public function testGetEnumOperators(): void {
        $enum    = __FUNCTION__;
        $alias   = 'alias';
        $scalars = new Scalars([
            $enum                 => [Equal::class, Equal::class],
            $alias                => $enum,
            Directive::ScalarEnum => [NotEqual::class, NotEqual::class],
        ]);

        $this->assertEquals([NotEqual::class], $scalars->getEnumOperators('unknown', false));
        $this->assertEquals(
            [NotEqual::class, IsNull::class, IsNotNull::class],
            $scalars->getEnumOperators('unknown', true),
        );
        $this->assertEquals([Equal::class], $scalars->getEnumOperators($enum, false));
        $this->assertEquals(
            [Equal::class, IsNull::class, IsNotNull::class],
            $scalars->getEnumOperators($enum, true),
        );
        $this->assertEquals(
            $scalars->getEnumOperators($enum, false),
            $scalars->getEnumOperators($alias, false),
        );
        $this->assertEquals(
            $scalars->getEnumOperators($enum, true),
            $scalars->getEnumOperators($alias, true),
        );
    }
}
