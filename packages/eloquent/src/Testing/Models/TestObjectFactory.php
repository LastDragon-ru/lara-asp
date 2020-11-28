<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Testing\Models;

use LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories\Factory;

/**
 * @internal
 */
class TestObjectFactory extends Factory {
    protected $model = TestObject::class;

    public function definition(): array {
        return [
            'value' => $this->faker->word,
        ];
    }
}
