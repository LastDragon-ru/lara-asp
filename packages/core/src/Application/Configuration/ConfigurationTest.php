<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Application\Configuration;

use LastDragon_ru\LaraASP\Core\Package\TestCase;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Configuration::class)]
final class ConfigurationTest extends TestCase {
    public function testSetState(): void {
        $expected    = new ConfigurationTest_ConfigurationA();
        $expected->a = 321;
        $expected->b = new ConfigurationTest_ConfigurationB();
        $actual      = ConfigurationTest_ConfigurationA::__set_state([
            'a' => 321,
            'b' => new ConfigurationTest_ConfigurationB(),
        ]);

        self::assertEquals($expected, $actual);
    }

    public function testOffsetGet(): void {
        self::expectException(LogicException::class);

        $config = new ConfigurationTest_ConfigurationA();

        $config['a']; // @phpstan-ignore expr.resultUnused (for test)
    }

    public function testOffsetExists(): void {
        self::expectException(LogicException::class);

        $config = new ConfigurationTest_ConfigurationA();

        isset($config['a']); // @phpstan-ignore expr.resultUnused (for test)
    }

    public function testOffsetUnset(): void {
        self::expectException(LogicException::class);

        $config = new ConfigurationTest_ConfigurationA();

        unset($config['a']);
    }

    public function testOffsetSet(): void {
        self::expectException(LogicException::class);

        $config = new ConfigurationTest_ConfigurationA();

        $config['a'] = 123;
    }

    public function testFromArray(): void {
        $expected        = new ConfigurationTest_ConfigurationA();
        $expected->a     = 321;
        $expected->b     = new ConfigurationTest_ConfigurationB();
        $expected->b->b  = true;
        $expected->b->bA = 'cba';
        $actual          = ConfigurationTest_ConfigurationA::fromArray([
            'a' => 321,
            'b' => [
                'b'   => true,
                'b_a' => 'cba',
            ],
        ]);

        self::assertEquals($expected, $actual);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ConfigurationTest_ConfigurationA extends Configuration {
    public function __construct(
        public int $a = 123,
        public ?ConfigurationTest_ConfigurationB $b = null,
    ) {
        parent::__construct();
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class ConfigurationTest_ConfigurationB extends Configuration {
    public function __construct(
        public bool $b = false,
        public string $bA = 'abc',
    ) {
        parent::__construct();
    }
}
