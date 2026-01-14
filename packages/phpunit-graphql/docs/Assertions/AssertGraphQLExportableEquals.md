# `assertGraphQLExportableEquals`

Exports and compares two GraphQL schemas/types/nodes/etc.

[include:example]: ./AssertGraphQLExportableEqualsTest.php
[//]: # (start: preprocess/b0cd9079e3494ea6)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\GraphQL\Docs\Assertions;

use GraphQL\Utils\BuildSchema;
use LastDragon_ru\PhpUnit\GraphQL\Assertions;
use LastDragon_ru\PhpUnit\GraphQL\Expected;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
final class AssertGraphQLExportableEqualsTest extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use Assertions;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        // Prepare
        $schema   = BuildSchema::build(
            <<<'GRAPHQL'
            directive @a(b: B) on OBJECT

            type Query {
                a: A
            }

            type A @a {
                id: ID!
            }

            input B {
                b: String!
            }
            GRAPHQL,
        );
        $type     = $schema->getType('A');
        $expected = <<<'GRAPHQL'
            type A
            @a
            {
                id: ID!
            }

            directive @a(
                b: B
            )
            on
                | OBJECT

            input B {
                b: String!
            }

            GRAPHQL;

        self::assertNotNull($type);

        // Test
        // (schema required to find types/directives definition)
        $this->assertGraphQLExportableEquals(
            new Expected($expected, schema: $schema),
            $type,
        );
    }
}
```

[//]: # (end: preprocess/b0cd9079e3494ea6)
