# `assertQueryLogEquals`

Asserts that `QueryLog` equals `QueryLog`.

[include:example]: ./AssertQueryLogEqualsTest.php
[//]: # (start: 3ef530658feaa07626c932caaaa1e1815225f65d1c36369abd30c445d65f18af)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LastDragon_ru\LaraASP\Testing\Concerns\DatabaseQueryComparator;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog;
use LogicException;
use Orchestra\Testbench\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
final class AssertQueryLogEqualsTest extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use WithQueryLog;
    use DatabaseQueryComparator;

    #[Override]
    protected function app(): Application {
        return $this->app ?? throw new LogicException('Application not yet initialized.');
    }

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

[//]: # (end: 3ef530658feaa07626c932caaaa1e1815225f65d1c36369abd30c445d65f18af)
