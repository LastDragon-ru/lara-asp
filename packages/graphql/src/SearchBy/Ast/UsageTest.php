<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use LogicException;
use OutOfBoundsException;
use stdClass;
use function sprintf;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Usage
 */
class UsageTest extends TestCase {
    /**
     * @covers ::get
     * @covers ::start
     * @covers ::end
     * @covers ::addValue
     * @covers ::addType
     */
    public function testUsage(): void {
        $usage   = new Usage();
        $classAA = new class() extends stdClass {
            // empty
        };
        $classAB = new class() extends stdClass {
            // empty
        };
        $classBA = new class() extends stdClass {
            // empty
        };
        $classBB = new class() extends stdClass {
            // empty
        };
        $classCA = new class() extends stdClass {
            // empty
        };
        $classCB = new class() extends stdClass {
            // empty
        };
        $classDA = new class() extends stdClass {
            // empty
        };

        $a = $usage->start('A');
        $usage->addValue($classAA::class);
        $b = $usage->start('B');
        $usage->addValue($classBA::class, $classBA::class, $classBB::class);
        $usage->end($b);
        $ca = $usage->start('C');
        $usage->addValue($classCA::class);
        $cb = $usage->start('C');
        $usage->addValue($classCB::class);
        $usage->end($cb);
        $usage->end($ca);
        $usage->addValue($classAB::class);
        $usage->end($a);
        $d = $usage->start('D');
        $usage->addValue($classDA::class);
        $usage->addType('B');
        $usage->end($d);

        self::assertEqualsCanonicalizing([
            $classAA::class,
            $classBA::class,
            $classBB::class,
            $classCA::class,
            $classAB::class,
            $classCB::class,
        ], $usage->get('A'));
        self::assertEqualsCanonicalizing([
            $classBA::class,
            $classBB::class,
        ], $usage->get('B'));
        self::assertEqualsCanonicalizing([
            $classCA::class,
            $classCB::class,
        ], $usage->get('C'));
        self::assertEqualsCanonicalizing([
            $classBA::class,
            $classBB::class,
            $classDA::class,
        ], $usage->get('D'));
    }

    /**
     * @covers ::end
     */
    public function testEndWithoutStart(): void {
        self::expectExceptionObject(new LogicException('Stack is empty.'));

        (new Usage())->end(1);
    }

    /**
     * @covers ::end
     */
    public function testEndInvalidIndex(): void {
        self::expectExceptionObject(new OutOfBoundsException(sprintf(
            'Index mismatch: required `%s`, `%s` given.',
            0,
            123,
        )));

        $usage = new Usage();

        $usage->start('A');
        $usage->end(123);
    }

    /**
     * @covers ::addValue
     */
    public function testAddValueWithoutStart(): void {
        $usage = new Usage();
        $usage->addValue(stdClass::class);

        self::expectExceptionObject(new LogicException('Stack is empty.'));

        $usage->end(0);
    }
}
