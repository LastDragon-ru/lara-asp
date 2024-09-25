# `assertJsonMatchesSchema`

Asserts that JSON matches [schema](https://json-schema.org/). Validation based on the [Opis JSON Schema](https://github.com/opis/json-schema) package.

[include:example]: ./AssertJsonMatchesSchemaTest.php
[//]: # (start: preprocess/70af41f7a1d4e860)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use LastDragon_ru\LaraASP\Testing\Assertions\JsonAssertions;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchemaValue;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
final class AssertJsonMatchesSchemaTest extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use JsonAssertions;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        self::assertJsonMatchesSchema(
            new JsonSchemaValue(
                <<<'JSON'
                {
                    "$schema": "https://json-schema.org/draft/2020-12/schema",
                    "type": "object",
                    "properties": {
                        "id": {
                            "type": "string",
                            "format": "uuid"
                        },
                        "title": {
                            "type": "string"
                        }
                    },
                    "required": [
                        "id"
                    ]
                }
                JSON,
            ),
            <<<'JSON'
            {
                "id": "8b658483-a17d-44b5-a024-8078a8eb039b"
            }
            JSON,
        );
    }
}
```

[//]: # (end: preprocess/70af41f7a1d4e860)
