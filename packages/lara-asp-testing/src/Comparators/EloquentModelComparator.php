<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use Illuminate\Database\Eloquent\Model;
use Override;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use stdClass;

use function substr_replace;

/**
 * Compares two Eloquent Models.
 *
 * The problem is models after creating from the factory and selecting from
 * the database may have different types for the same properties. For example,
 * `factory()->create()` will set `key` as `int`, but `select` will set it to
 * `string` and (strict) comparison will fail. This comparator normalizes
 * properties types before comparison.
 *
 * @see https://github.com/laravel/ideas/issues/1914
 */
class EloquentModelComparator extends Comparator {
    #[Override]
    public function accepts(mixed $expected, mixed $actual): bool {
        return $expected instanceof Model
            && $actual instanceof Model;
    }

    #[Override]
    public function assertEquals(
        mixed $expected,
        mixed $actual,
        float $delta = 0.0,
        bool $canonicalize = false,
        bool $ignoreCase = false,
    ): void {
        // Comparator
        $comparator = $this->factory()->getComparatorFor(new stdClass(), new stdClass());

        // If classes different we just call parent to fail
        if (!($actual instanceof Model) || !($expected instanceof Model) || $actual::class !== $expected::class) {
            $comparator->assertEquals($expected, $actual, $delta, $canonicalize, $ignoreCase);

            return;
        }

        // Normalize properties and compare
        $normalizedExpected = $this->normalize($expected);
        $normalizedActual   = $this->normalize($actual);

        try {
            $comparator->assertEquals($normalizedExpected, $normalizedActual, $delta, $canonicalize, $ignoreCase);
        } catch (ComparisonFailure $e) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                substr_replace($e->getExpectedAsString(), $expected::class.' Model', 0, 5),
                substr_replace($e->getActualAsString(), $actual::class.' Model', 0, 5),
                'Failed asserting that two models are equal.',
            );
        }
    }

    protected function normalize(Model $model): Model {
        // We don't want update original model
        $model = clone $model;

        // Normalize attributes
        foreach ($model->getAttributes() as $key => $value) {
            // This will force Laravel to convert properties into the right types.
            $model->setAttribute($key, $model->getAttribute($key));

            // We also need sync `original`
            if ($model->isClean($key)) {
                $model->syncOriginalAttribute($key);
            }
        }

        // Return
        return $model;
    }
}
