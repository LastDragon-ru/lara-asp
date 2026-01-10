<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mockery;

/**
 * @experimental
 */
interface PropertiesMock {
    public function shouldUseProperty(string $name): MockedProperty;
}
