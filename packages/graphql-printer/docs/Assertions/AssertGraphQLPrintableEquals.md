# `assertGraphQLPrintableEquals`

Prints and compares two GraphQL schemas/types/nodes/etc.

[include:example]: ./AssertGraphQLPrintableEqualsTest.php
[//]: # (start: 4db955285a38dee14fe701f8fbe05e7862fd773a0ea1c03e91009e86bbc0ca22)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Docs\Assertions;

use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLAssertions;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
final class AssertGraphQLPrintableEqualsTest extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use GraphQLAssertions;

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        // Prepare
        $schema = BuildSchema::build(
            <<<'GRAPHQL'
            type Query {
                a: A
            }

            type A @a {
                id: ID!
            }

            directive @a on OBJECT
            GRAPHQL,
        );
        $type   = $schema->getType('A');

        self::assertNotNull($type);

        // Test
        $this->assertGraphQLPrintableEquals(
            <<<'GRAPHQL'
            type A
            @a
            {
                id: ID!
            }
            GRAPHQL,
            $type,
        );
    }
}
```

[//]: # (end: 4db955285a38dee14fe701f8fbe05e7862fd773a0ea1c03e91009e86bbc0ca22)
