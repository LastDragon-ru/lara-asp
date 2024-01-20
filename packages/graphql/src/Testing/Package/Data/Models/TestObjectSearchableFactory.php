<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Data\Models;

use LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @internal
 *
 * @extends Factory<TestObjectSearchable>
 */
class TestObjectSearchableFactory extends Factory {
    /**
     * @var class-string<TestObjectSearchable>
     */
    protected $model = TestObjectSearchable::class;

    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function definition(): array {
        return [
            'id'    => $this->faker->uuid(),
            'value' => $this->faker->word(),
        ];
    }
}
