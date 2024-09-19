<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ClassLink::class)]
final class ClassLinkTest extends TestCase {
    public function testGetTitle(): void {
        self::assertEquals('Class', (new ClassLink('Class'))->getTitle());
        self::assertEquals('Class', (new ClassLink('App\\Class',))->getTitle());
        self::assertEquals('Class', (new ClassLink('\\App\\Class'))->getTitle());
    }
}
