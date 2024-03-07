# `assertQueryLogEquals`

Asserts that `QueryLog` equals `QueryLog`.

[include:example]: ./AssertQueryLogEquals.php
[//]: # (start: a0daa9b516048283a6d056944632ce1755af092cb15ddba58bd54c897066dcc4)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LastDragon_ru\LaraASP\Testing\Concerns\DatabaseQueryComparator;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
final class AssertQueryLogEquals extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use WithQueryLog;
    use DatabaseQueryComparator;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        Schema::create('test_table', static function ($table): void {
            $table->string('a')->nullable();
            $table->string('b')->nullable();
            $table->string('c')->nullable();
        });

        DB::table('test_table')
            ->select('a, b, c')
            ->get();

        $queries = $this->getQueryLog();

        DB::table('test_table')
            ->select('a, b, c')
            ->where('a', '=', 'value')
            ->orderBy('a')
            ->get();

        self::assertQueryLogEquals(
            [
                [
                    'query'    => 'select "a, b, c" from "test_table" where "a" = ? order by "a" asc',
                    'bindings' => [
                        'value',
                    ],
                ],
            ],
            $queries,
        );
    }
}
```

Example output:

```plain
Tests: 1, Assertions: 0, Errors: 1.
```

[//]: # (end: a0daa9b516048283a6d056944632ce1755af092cb15ddba58bd54c897066dcc4)
