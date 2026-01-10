# `assertDirectoryEquals`

Asserts that Directory equals Directory.

[include:example]: ./AssertDirectoryEqualsTest.php
[//]: # (start: preprocess/9cdda34ca973d134)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use LastDragon_ru\LaraASP\Testing\Assertions\FileSystemAssertions;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
final class AssertDirectoryEqualsTest extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use FileSystemAssertions;
    use WithTestData;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        self::assertDirectoryEquals(
            self::getTestData()->path('a'),
            self::getTestData()->path('b'),
        );
    }
}
```

[//]: # (end: preprocess/9cdda34ca973d134)
