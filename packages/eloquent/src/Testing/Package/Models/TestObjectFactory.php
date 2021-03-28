<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models;

use LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories\Factory;

/**
 * @internal
 */
class TestObjectFactory extends Factory {
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected $model = TestObject::class;

    /**
     * @return array<string,mixed>
     */
    public function definition(): array {
        return [
            'value' => $this->faker->word,
        ];
    }
}
