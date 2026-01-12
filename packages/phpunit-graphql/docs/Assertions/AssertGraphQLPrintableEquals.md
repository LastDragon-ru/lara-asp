# `assertGraphQLPrintableEquals`

Prints and compares two GraphQL schemas/types/nodes/etc.

[include:example]: ./AssertGraphQLPrintableEqualsTest.php
[//]: # (start: preprocess/7020c87844f6795d)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\GraphQL\Docs\Assertions;

use GraphQL\Utils\BuildSchema;
use LastDragon_ru\PhpUnit\GraphQL\Assertions;
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
    use Assertions;

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

[//]: # (end: preprocess/7020c87844f6795d)
