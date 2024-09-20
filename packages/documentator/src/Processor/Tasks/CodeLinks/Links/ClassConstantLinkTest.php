<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ClassConstantLink::class)]
final class ClassConstantLinkTest extends TestCase {
    public function testToString(): void {
        self::assertEquals('Class::Constant', (string) new ClassConstantLink('Class', 'Constant'));
        self::assertEquals('App\\Class::Constant', (string) new ClassConstantLink('App\\Class', 'Constant'));
        self::assertEquals('\\App\\Class::Constant', (string) new ClassConstantLink('\\App\\Class', 'Constant'));
    }

    public function testGetTitle(): void {
        self::assertEquals('Class::Constant', (new ClassConstantLink('Class', 'Constant'))->getTitle());
        self::assertEquals('Class::Constant', (new ClassConstantLink('App\\Class', 'Constant'))->getTitle());
        self::assertEquals('Class::Constant', (new ClassConstantLink('\\App\\Class', 'Constant'))->getTitle());
    }
}
