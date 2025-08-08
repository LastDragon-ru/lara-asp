<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package\Data\Models;

use LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @internal
 *
 * @extends Factory<TestObject>
 */
class TestObjectFactory extends Factory {
    /**
     * @var class-string<TestObject>
     */
    protected $model = TestObject::class;

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
