<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Factory::class)]
final class FactoryTest extends TestCase {
    public function testParse(): void {
        $factory = new Factory();

        // Class
        self::assertEquals(
            new ClassLink('\\App\\Class'),
            $factory->create('\\App\\Class'),
        );
        self::assertEquals(
            new ClassLink('\\App\\Class'),
            $factory->create('App\\Class'),
        );
        self::assertEquals(
            new ClassLink('\\Class'),
            $factory->create('\\Class'),
        );
        self::assertEquals(
            new ClassLink('\\Class'),
            $factory->create('Class'),
        );
        self::assertEquals(
            new ClassLink('\\App\\Class'),
            $factory->create('Class', static fn ($class) => "App\\{$class}"),
        );
        self::assertNull(
            $factory->create('Class', static fn ($class) => null),
        );

        // Class Method
        self::assertEquals(
            new ClassMethodLink('\\App\\Class', 'method'),
            $factory->create('\\App\\Class::method()'),
        );
        self::assertEquals(
            new ClassMethodLink('\\App\\Class', 'method'),
            $factory->create('App\\Class::method()'),
        );

        // Class Const
        self::assertEquals(
            new ClassConstantLink('\\App\\Class', 'constant'),
            $factory->create('\\App\\Class::constant'),
        );
        self::assertEquals(
            new ClassConstantLink('\\App\\Class', 'constant'),
            $factory->create('App\\Class::constant'),
        );

        // Class Property
        self::assertEquals(
            new ClassPropertyLink('\\App\\Class', 'property'),
            $factory->create('\\App\\Class::$property'),
        );
        self::assertEquals(
            new ClassPropertyLink('\\App\\Class', 'property'),
            $factory->create('App\\Class::$property'),
        );

        // Whitespace
        self::assertEquals(
            new ClassLink('\\App\\Class'),
            $factory->create('  \\App\\Class'),
        );
    }
}
