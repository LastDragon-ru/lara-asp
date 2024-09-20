<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ClassPropertyLink::class)]
final class ClassPropertyLinkTest extends TestCase {
    public function testToString(): void {
        self::assertEquals('Class::$property', (string) new ClassPropertyLink('Class', 'property'));
        self::assertEquals('App\\Class::$property', (string) new ClassPropertyLink('App\\Class', 'property'));
        self::assertEquals('\\App\\Class::$property', (string) new ClassPropertyLink('\\App\\Class', 'property'));
    }

    public function testGetTitle(): void {
        self::assertEquals('Class::$property', (new ClassPropertyLink('Class', 'property'))->getTitle());
        self::assertEquals('Class::$property', (new ClassPropertyLink('App\\Class', 'property'))->getTitle());
        self::assertEquals('Class::$property', (new ClassPropertyLink('\\App\\Class', 'property'))->getTitle());
    }
}
