# `assertGraphQLSchemaEquals`

Compares default schema.

[include:example]: ./AssertGraphQLSchemaEquals.php
[//]: # (start: c27d84ed181bcfd0818ca15c55ac1ae5741cb924799f404121378db2cc219470)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Docs\Assertions;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\Provider;
use LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Directives\TestDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Provider as TestProvider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Override;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
final class AssertGraphQLSchemaEquals extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use GraphQLAssertions;

    /**
     * Preparation for test.
     *
     * @inheritDoc
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array {
        return [
            Provider::class,
            TestProvider::class,
            LighthouseServiceProvider::class,
        ];
    }

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        // Prepare
        Container::getInstance()->make(DirectiveLocator::class)
            ->setResolved('a', TestDirective::class)
            ->setResolved('test', TestDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            directive @a(b: B) on OBJECT

            type Query {
                a: A @test
            }

            type A @a {
                id: ID!
            }

            input B {
                b: String!
            }
            GRAPHQL,
        );

        // Test
        $this->assertGraphQLSchemaEquals(
            <<<'GRAPHQL'
            directive @a(
                b: B
            )
            on
                | OBJECT

            directive @test
            on
                | FIELD_DEFINITION

            input B {
                b: String!
            }

            type A
            @a
            {
                id: ID!
            }

            type Query {
                a: A
                @test
            }

            GRAPHQL,
        );
    }
}
```

Example output:

```plain
OK (1 test, 1 assertion)
```

[//]: # (end: c27d84ed181bcfd0818ca15c55ac1ae5741cb924799f404121378db2cc219470)
