# `assertThatResponse` 💀

Asserts that PSR Response satisfies given constraint (we have a lot of built-in [constraints](../../src/Constraints/Response) and [responses](../../src/Responses), but, of course, you can create a custom).

> [!WARNING]
>
> Assertion is deprecated, please use [`assertPsrResponse()`](./AssertPsrResponse.md) instead.

[include:example]: ./AssertThatResponseTest.php
[//]: # (start: preprocess/abd3acf8fed8e92d)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Docs\Assertions;

use GuzzleHttp\Psr7\Response as HttpResponse;
use LastDragon_ru\LaraASP\Testing\Assertions\ResponseAssertions;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonFragmentMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesFragment;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchemaValue;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies\JsonBody;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
final class AssertThatResponseTest extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use ResponseAssertions;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        // Prepare
        $response = new HttpResponse(
            200,
            [
                'Content-Type' => 'application/json',
            ],
            <<<'JSON'
            {
                "data": {
                    "product": {
                        "id": "5d0c7267-cbab-4539-8b66-1e016f6dd1bd"
                    }
                }
            }
            JSON,
        );

        // Test
        self::assertThatResponse(
            $response,
            new Response(
                new StatusCode(200),
                new ContentType('application/json'),
                new JsonBody(
                    new JsonFragmentMatchesSchema(
                        'data.product',
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
                    ),
                    new JsonMatchesFragment(
                        'data.product',
                        [
                            'id' => '5d0c7267-cbab-4539-8b66-1e016f6dd1bd',
                        ],
                    ),
                ),
            ),
        );
    }
}
```

[//]: # (end: preprocess/abd3acf8fed8e92d)
