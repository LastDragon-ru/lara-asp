<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Reference;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Reference::class)]
final class ReferenceTest extends TestCase {
    public function testParse(): void {
        // Class
        self::assertEquals(
            new ClassReference('\\App\\Class'),
            Reference::parse('\\App\\Class'),
        );
        self::assertEquals(
            new ClassReference('\\App\\Class'),
            Reference::parse('App\\Class'),
        );
        self::assertEquals(
            new ClassReference('\\Class'),
            Reference::parse('\\Class'),
        );
        self::assertEquals(
            new ClassReference('\\Class'),
            Reference::parse('Class'),
        );
        self::assertEquals(
            new ClassReference('\\App\\Class'),
            Reference::parse('Class', static fn($class) => "App\\{$class}"),
        );
        self::assertNull(
            Reference::parse('Class', static fn($class) => null),
        );

        // Class Method
        self::assertEquals(
            new ClassMethodReference('\\App\\Class', 'method'),
            Reference::parse('\\App\\Class::method()'),
        );
        self::assertEquals(
            new ClassMethodReference('\\App\\Class', 'method'),
            Reference::parse('App\\Class::method()'),
        );

        // Class Const
        self::assertEquals(
            new ClassConstantReference('\\App\\Class', 'constant'),
            Reference::parse('\\App\\Class::constant'),
        );
        self::assertEquals(
            new ClassConstantReference('\\App\\Class', 'constant'),
            Reference::parse('App\\Class::constant'),
        );

        // Class Property
        self::assertEquals(
            new ClassPropertyReference('\\App\\Class', 'property'),
            Reference::parse('\\App\\Class::$property'),
        );
        self::assertEquals(
            new ClassPropertyReference('\\App\\Class', 'property'),
            Reference::parse('App\\Class::$property'),
        );
    }
}
