<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Mockery;

use Mockery\Generator\MockConfigurationBuilder;
use Override;

/**
 * Customized builder that automatically adds {@see MockProperties} trait into
 * mock. The class is required because PHPStan cannot create intersection/union
 * from traits so direct usage of {@see MockProperties} will give us `*NEVER*`.
 *
 * @see https://github.com/mockery/mockery/issues/1142
 * @see https://github.com/phpstan/phpstan-mockery/issues/78
 *
 * @experimental
 */
class WithProperties extends MockConfigurationBuilder {
    #[Override]
    public function addTarget(mixed $target): MockConfigurationBuilder {
        if ($target === PropertiesMock::class) {
            $target = MockProperties::class;
        }

        return parent::addTarget($target);
    }
}
