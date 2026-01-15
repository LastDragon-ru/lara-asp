# `assertScoutQueryEquals`

Asserts that Scout Query equals Scout Query.

[include:example]: ./AssertScoutQueryEqualsTest.php
[//]: # (start: preprocess/085affb973aa23e4)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use LastDragon_ru\LaraASP\Testing\Assertions\ScoutAssertions;
use LastDragon_ru\PhpUnit\Extensions\Requirements\Attributes\RequiresPackage;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
#[RequiresPackage('laravel/scout')]
final class AssertScoutQueryEqualsTest extends TestCase {
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

[//]: # (end: preprocess/085affb973aa23e4)
