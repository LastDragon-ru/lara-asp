<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use Exception;
use Hamcrest\Core\IsNot;
use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ScalarNoOperators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\ScalarUnknown;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNotNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\IsNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\NotEqual;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery\MockInterface;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Scalars
 */
class ScalarsTest extends TestCase {
    // <editor-fold desc="Init">
    // =========================================================================
    /**
     * @before
     */
    public function init(): void {
        $this->afterApplicationCreated(function (): void {
            $this->override(Repository::class, static function (MockInterface $mock): void {
                $mock
                    ->shouldReceive('get')
                    ->andReturn(null);
            });
        });
    }
    // </editor-fold>

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::isScalar
     */
    public function testIsScalar(): void {
        $scalars = $this->app->make(Scalars::class);

        self::assertTrue($scalars->isScalar(Directive::ScalarInt));
        self::assertFalse($scalars->isScalar('unknown'));
    }

    /**
     * @covers ::addScalar
     *
     * @dataProvider dataProviderAddScalar
     */
    public function testAddScalar(Exception|bool $expected, string $scalar, mixed $operators): void {
        if ($expected instanceof Exception) {
            self::expectExceptionObject($expected);
        }

        $scalars = $this->app->make(Scalars::class);

        $scalars->addScalar($scalar, $operators);

        self::assertEquals($expected, $scalars->isScalar($scalar));
    }

    /**
     * @covers ::getScalarOperators
     */
    public function testGetScalarOperators(): void {
        $scalar  = __FUNCTION__;
        $alias   = 'alias';
        $scalars = $this->app->make(Scalars::class);

        $scalars->addScalar($scalar, [Equal::class, Equal::class]);
        $scalars->addScalar($alias, $scalar);

        self::assertEquals(
            [Equal::class],
            $this->toClassNames($scalars->getScalarOperators($scalar, false)),
        );
        self::assertEquals(
            [Equal::class, IsNull::class, IsNotNull::class],
            $this->toClassNames($scalars->getScalarOperators($scalar, true)),
        );
        self::assertEquals(
            $scalars->getScalarOperators($scalar, false),
            $scalars->getScalarOperators($alias, false),
        );
        self::assertEquals(
            $scalars->getScalarOperators($scalar, true),
            $scalars->getScalarOperators($alias, true),
        );
    }

    /**
     * @covers ::getScalarOperators
     */
    public function testGetScalarOperatorsUnknownScalar(): void {
        self::expectExceptionObject(new ScalarUnknown('unknown'));

        $this->app->make(Scalars::class)->getScalarOperators('unknown', false);
    }

    /**
     * @covers ::getEnumOperators
     */
    public function testGetEnumOperators(): void {
        $enum    = __FUNCTION__;
        $alias   = 'alias';
        $scalars = $this->app->make(Scalars::class);

        $scalars->addScalar($enum, [Equal::class, Equal::class]);
        $scalars->addScalar($alias, $enum);
        $scalars->addScalar(Directive::ScalarEnum, [NotEqual::class, NotEqual::class]);

        self::assertEquals(
            [NotEqual::class],
            $this->toClassNames($scalars->getEnumOperators('unknown', false)),
        );
        self::assertEquals(
            [NotEqual::class, IsNull::class, IsNotNull::class],
            $this->toClassNames($scalars->getEnumOperators('unknown', true)),
        );
        self::assertEquals(
            [Equal::class],
            $this->toClassNames($scalars->getEnumOperators($enum, false)),
        );
        self::assertEquals(
            [Equal::class, IsNull::class, IsNotNull::class],
            $this->toClassNames($scalars->getEnumOperators($enum, true)),
        );
        self::assertEquals(
            $scalars->getEnumOperators($enum, false),
            $scalars->getEnumOperators($alias, false),
        );
        self::assertEquals(
            $scalars->getEnumOperators($enum, true),
            $scalars->getEnumOperators($alias, true),
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderAddScalar(): array {
        return [
            'ok'              => [true, 'scalar', [IsNot::class]],
            'unknown scalar'  => [
                new ScalarUnknown('unknown'),
                'scalar',
                'unknown',
            ],
            'empty operators' => [
                new ScalarNoOperators('scalar'),
                'scalar',
                [],
            ],
        ];
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @param array<object> $objects
     *
     * @return array<class-string>
     */
    protected function toClassNames(array $objects): array {
        $classes = [];

        foreach ($objects as $object) {
            $classes[] = $object::class;
        }

        return $classes;
    }
    // </editor-fold>
}
