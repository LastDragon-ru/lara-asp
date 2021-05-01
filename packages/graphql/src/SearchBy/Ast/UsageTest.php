<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use LogicException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

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
     * @covers ::addValues
     */
    public function testUsage(): void {
        $usage = new Usage();

        $a = $usage->start('A');
        $usage->addValue('a');
        $b = $usage->start('B');
        $usage->addValues(['b', 'b', 'bb']);
        $usage->end($b);
        $c = $usage->start('C');
        $usage->addValue('c');
        $usage->end($c);
        $usage->addValue('aa');
        $usage->end($a);

        $this->assertEqualsCanonicalizing(['a', 'b', 'bb', 'c', 'aa'], $usage->get('A'));
        $this->assertEqualsCanonicalizing(['b', 'bb'], $usage->get('B'));
        $this->assertEqualsCanonicalizing(['c'], $usage->get('C'));
    }

    /**
     * @covers ::end
     */
    public function testEndWithoutStart(): void {
        $this->expectExceptionObject(new LogicException('Stack is empty.'));

        (new Usage())->end(1);
    }

    /**
     * @covers ::end
     */
    public function testEndInvalidIndex(): void {
        $this->expectExceptionObject(new OutOfBoundsException(sprintf(
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
        (new Usage())->addValue(1);

        $this->assertTrue(true);
    }

    /**
     * @covers ::addValues
     */
    public function testAddValuesWithoutStart(): void {
        (new Usage())->addValues([1]);

        $this->assertTrue(true);
    }
}
