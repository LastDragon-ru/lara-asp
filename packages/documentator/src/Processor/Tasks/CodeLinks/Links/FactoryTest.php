<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClassComment;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Factory::class)]
final class FactoryTest extends TestCase {
    public function testParse(): void {
        $comment = Mockery::mock(PhpClassComment::class);
        $factory = new Factory($comment);

        // Class
        self::assertEquals(
            new ClassLink($comment, '\\App\\Class'),
            $factory->create('\\App\\Class'),
        );
        self::assertEquals(
            new ClassLink($comment, '\\App\\Class'),
            $factory->create('App\\Class'),
        );
        self::assertEquals(
            new ClassLink($comment, '\\Class'),
            $factory->create('\\Class'),
        );
        self::assertEquals(
            new ClassLink($comment, '\\Class'),
            $factory->create('Class'),
        );
        self::assertEquals(
            new ClassLink($comment, '\\App\\Class'),
            $factory->create('Class', static fn($class) => "App\\{$class}"),
        );
        self::assertNull(
            $factory->create('Class', static fn($class) => null),
        );

        // Class Method
        self::assertEquals(
            new ClassMethodLink($comment, '\\App\\Class', 'method'),
            $factory->create('\\App\\Class::method()'),
        );
        self::assertEquals(
            new ClassMethodLink($comment, '\\App\\Class', 'method'),
            $factory->create('App\\Class::method()'),
        );

        // Class Const
        self::assertEquals(
            new ClassConstantLink($comment, '\\App\\Class', 'constant'),
            $factory->create('\\App\\Class::constant'),
        );
        self::assertEquals(
            new ClassConstantLink($comment, '\\App\\Class', 'constant'),
            $factory->create('App\\Class::constant'),
        );

        // Class Property
        self::assertEquals(
            new ClassPropertyLink($comment, '\\App\\Class', 'property'),
            $factory->create('\\App\\Class::$property'),
        );
        self::assertEquals(
            new ClassPropertyLink($comment, '\\App\\Class', 'property'),
            $factory->create('App\\Class::$property'),
        );

        // Whitespace
        self::assertEquals(
            new ClassLink($comment, '\\App\\Class'),
            $factory->create('  \\App\\Class'),
        );
    }
}
