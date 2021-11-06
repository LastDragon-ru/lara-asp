<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Testing\Package\Models;

use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories\Factory;

/**
 * @internal
 */
class TestObjectFactory extends Factory {
    /**
     * @var class-string<Model>
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
