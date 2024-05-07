# `assertDatabaseQueryEquals`

Asserts that SQL Query equals SQL Query.

[include:example]: ./AssertDatabaseQueryEqualsTest.php
[//]: # (start: 65d62248ad4d9d80f20c4988a5dfc7a946864fb5d63547ccee2b721b84c9b46f)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use Illuminate\Support\Facades\DB;
use LastDragon_ru\LaraASP\Testing\Assertions\DatabaseAssertions;
use LastDragon_ru\LaraASP\Testing\Concerns\DatabaseQueryComparator;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
final class AssertDatabaseQueryEqualsTest extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use DatabaseAssertions;
    use DatabaseQueryComparator;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        self::assertDatabaseQueryEquals(
            [
                'query'    => <<<'SQL'
                    select "a, b, c"
                    from "test_table"
                    where "a" = ? and "b" between ? and ?
                    order by "a" asc
                    SQL
                ,
                'bindings' => [
                    'value',
                    10,
                    100,
                ],
            ],
            DB::table('test_table')
                ->select('a, b, c')
                ->where('a', '=', 'value')
                ->whereBetween('b', [10, 100])
                ->orderBy('a'),
        );
    }
}
```

[//]: # (end: 65d62248ad4d9d80f20c4988a5dfc7a946864fb5d63547ccee2b721b84c9b46f)
