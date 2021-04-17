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
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Types
 */
class TypesTest extends TestCase {
    /**
     * @covers ::isScalar
     */
    public function testIsScalar(): void {
        $this->assertTrue((new Types())->isScalar(Directive::ScalarInt));
        $this->assertFalse((new Types())->isScalar('unknown'));
    }

    /**
     * @covers ::getScalarOperators
     */
    public function testGetScalarOperators(): void {
        $scalar = __FUNCTION__;
        $alias  = 'alias';
        $types  = new Types([
            $scalar => [Equal::class, Equal::class],
            $alias  => $scalar,
        ]);

        $this->assertEquals([], $types->getScalarOperators('unknown', false));
        $this->assertEquals([IsNull::class, IsNotNull::class], $types->getScalarOperators('unknown', true));
        $this->assertEquals([Equal::class], $types->getScalarOperators($scalar, false));
        $this->assertEquals(
            [Equal::class, IsNull::class, IsNotNull::class],
            $types->getScalarOperators($scalar, true),
        );
        $this->assertEquals(
            $types->getScalarOperators($scalar, false),
            $types->getScalarOperators($alias, false),
        );
        $this->assertEquals(
            $types->getScalarOperators($scalar, true),
            $types->getScalarOperators($alias, true),
        );
    }

    /**
     * @covers ::getEnumOperators
     */
    public function testGetEnumOperators(): void {
        $enum  = __FUNCTION__;
        $alias = 'alias';
        $types = new Types([
            $enum                 => [Equal::class, Equal::class],
            $alias                => $enum,
            Directive::ScalarEnum => [NotEqual::class, NotEqual::class],
        ]);

        //$this->assertEquals([NotEqual::class], $types->getEnumOperators('unknown', false));
        $this->assertEquals(
            [NotEqual::class, IsNull::class, IsNotNull::class],
            $types->getEnumOperators('unknown', true),
        );
        $this->assertEquals([Equal::class], $types->getEnumOperators($enum, false));
        $this->assertEquals(
            [Equal::class, IsNull::class, IsNotNull::class],
            $types->getEnumOperators($enum, true),
        );
        $this->assertEquals(
            $types->getEnumOperators($enum, false),
            $types->getEnumOperators($alias, false),
        );
        $this->assertEquals(
            $types->getEnumOperators($enum, true),
            $types->getEnumOperators($alias, true),
        );
    }
}
