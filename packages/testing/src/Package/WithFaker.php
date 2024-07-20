<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Package;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\After;

/**
 * FakerPHP support.
 *
 * Required to avoid dependency from `Illuminate\Foundation\*`.
 *
 * @internal
 */
trait WithFaker {
    /**
     * @var array<string, Generator>
     */
    private array $withFaker = [];

    #[After]
    protected function withFakerAfter(): void {
        $this->withFaker = [];
    }

    protected function getFaker(?string $locale = null): Generator {
        $locale                   ??= Factory::DEFAULT_LOCALE;
        $this->withFaker[$locale] ??= Factory::create($locale);

        return $this->withFaker[$locale];
    }
}
