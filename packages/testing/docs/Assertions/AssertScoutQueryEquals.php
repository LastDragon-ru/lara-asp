<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use LastDragon_ru\LaraASP\Testing\Assertions\ScoutAssertions;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
final class AssertScoutQueryEquals extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use ScoutAssertions;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        self::assertScoutQueryEquals(
            [
                'query'    => '*',
                'wheres'   => [
                    'a' => 'value',
                ],
                'whereIns' => [
                    'b' => ['a', 'b', 'c'],
                ],
            ],
            (new Builder(
                new class() extends Model {
                    // empty
                },
                '*',
            ))
                ->where('a', 'value')
                ->whereIn('b', ['a', 'b', 'c']),
        );
    }
}
