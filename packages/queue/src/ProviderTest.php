<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Queue;

use AllowDynamicProperties;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Queue\Contracts\ConfigurableQueueable;
use LastDragon_ru\LaraASP\Queue\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Provider::class)]
class ProviderTest extends TestCase {
    public function testRegisterConfigurator(): void {
        $actual   = Container::getInstance()->make(ProviderTest_ConfigurableQueueable::class)->queue ?? null;
        $expected = 'test';

        self::assertEquals($expected, $actual);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
#[AllowDynamicProperties]
class ProviderTest_ConfigurableQueueable implements ConfigurableQueueable {
    /**
     * @inheritDoc
     */
    #[Override]
    public function getQueueConfig(): array {
        return [
            'queue' => 'test',
        ];
    }
}
