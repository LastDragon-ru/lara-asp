# `assertGraphQLExportableEquals`

Exports and compares two GraphQL schemas/types/nodes/etc.

[include:example]: ./AssertGraphQLExportableEquals.php
[//]: # (start: f557755e555b59192c22e63769cb3506d732684c28baab5b4407791d5675372d)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Docs\Assertions;

use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLAssertions;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpected;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
final class AssertGraphQLExportableEquals extends TestCase {
    use GraphQLAssertions;

    public function testAssertion(): void {
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

        $this->assertGraphQLExportableEquals(
            (new GraphQLExpected($expected))->setSchema($schema),
            $type,
        );
    }
}
```

Example output:

```plain
OK (1 test, 2 assertions)
```

[//]: # (end: f557755e555b59192c22e63769cb3506d732684c28baab5b4407791d5675372d)
