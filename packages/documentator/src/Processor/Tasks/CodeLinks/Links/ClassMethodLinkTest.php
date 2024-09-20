<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ClassMethodLink::class)]
final class ClassMethodLinkTest extends TestCase {
    public function testToString(): void {
        self::assertEquals('Class::method()', (string) new ClassMethodLink('Class', 'method'));
        self::assertEquals('App\\Class::method()', (string) new ClassMethodLink('App\\Class', 'method'));
        self::assertEquals('\\App\\Class::method()', (string) new ClassMethodLink('\\App\\Class', 'method'));
    }

    public function testGetTitle(): void {
        self::assertEquals('Class::method()', (new ClassMethodLink('Class', 'method'))->getTitle());
        self::assertEquals('Class::method()', (new ClassMethodLink('App\\Class', 'method'))->getTitle());
        self::assertEquals('Class::method()', (new ClassMethodLink('\\App\\Class', 'method'))->getTitle());
    }
}
