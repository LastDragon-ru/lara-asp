# `assertScoutQueryEquals`

Asserts that Scout Query equals Scout Query.

[include:example]: ./AssertScoutQueryEqualsTest.php
[//]: # (start: 095a46ecac5d8728830790ea09a3c4fe3fbecbc26ff470a7f1dfc4abe053801d)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use LastDragon_ru\LaraASP\Testing\Assertions\ScoutAssertions;
use LastDragon_ru\LaraASP\Testing\Requirements\Requirements\RequiresComposerPackage;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
#[RequiresComposerPackage('laravel/scout')]
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

[//]: # (end: 095a46ecac5d8728830790ea09a3c4fe3fbecbc26ff470a7f1dfc4abe053801d)
