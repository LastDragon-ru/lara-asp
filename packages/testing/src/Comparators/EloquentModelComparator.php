<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ObjectComparator;

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
class EloquentModelComparator extends ObjectComparator {
    /**
     * @inheritdoc
     */
    public function accepts($expected, $actual) {
        return $expected instanceof Model
            && $actual instanceof Model;
    }

    /**
     * @inheritdoc
     *
     * @param array<mixed> $processed
     */
    public function assertEquals(
        $expected,
        $actual,
        $delta = 0.0,
        $canonicalize = false,
        $ignoreCase = false,
        array &$processed = [],
    ): void {
        // If classes different we just call parent to fail
        if (!($actual instanceof Model) || !($expected instanceof Model) || $actual::class !== $expected::class) {
            parent::assertEquals($expected, $actual, $delta, $canonicalize, $ignoreCase, $processed);
        }

        // Normalize properties and compare
        $normalizedExpected = $this->normalize($expected);
        $normalizedActual   = $this->normalize($actual);

        try {
            parent::assertEquals(
                $normalizedExpected,
                $normalizedActual,
                $delta,
                $canonicalize,
                $ignoreCase,
                $processed,
            );
        } catch (ComparisonFailure $e) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                substr_replace($e->getExpectedAsString(), $expected::class.' Model', 0, 5),
                substr_replace($e->getActualAsString(), $actual::class.' Model', 0, 5),
                false,
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
