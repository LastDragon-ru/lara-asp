<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ClassConstantLink::class)]
final class ClassConstantLinkTest extends TestCase {
    public function testGetTitle(): void {
        self::assertEquals('Class::Constant', (new ClassConstantLink('Class', 'Constant'))->getTitle());
        self::assertEquals('Class::Constant', (new ClassConstantLink('App\\Class', 'Constant'))->getTitle());
        self::assertEquals('Class::Constant', (new ClassConstantLink('\\App\\Class', 'Constant'))->getTitle());
    }
}
