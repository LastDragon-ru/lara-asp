# `assertGraphQLPrintableEquals`

Prints and compares two GraphQL schemas/types/nodes/etc.

[include:example]: ./AssertGraphQLPrintableEquals.php
[//]: # (start: 605e6281cbe3b98cb8a0ab9cbe59b03cee95427b019b0db773f69a80c79ec3e6)
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
final class AssertGraphQLPrintableEquals extends TestCase {
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

Example output:

```plain
OK (1 test, 2 assertions)
```

[//]: # (end: 605e6281cbe3b98cb8a0ab9cbe59b03cee95427b019b0db773f69a80c79ec3e6)