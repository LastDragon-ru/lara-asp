# `assertScoutQueryEquals`

Asserts that Scout Query equals Scout Query.

[include:example]: ./AssertScoutQueryEquals.php
[//]: # (start: b9b47beb1873fcc14bf5d91e05842dff8f706ce96ec155913d5cf22c55e0d451)
[//]: # (warning: Generated automatically. Do not edit.)

```php
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
```

Example output:

```plain
OK (1 test, 1 assertion)
```

[//]: # (end: b9b47beb1873fcc14bf5d91e05842dff8f706ce96ec155913d5cf22c55e0d451)
