# `assertGraphQLSchemaEquals`

Compares default internal schema (with all directives).

[include:example]: ./AssertGraphQLSchemaEqualsTest.php
[//]: # (start: 9396b7581da4ef186d57181c7829cb37e837a31181b78e830df4567f39492bdc)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Docs\Assertions;

use LastDragon_ru\LaraASP\Core\Provider as CoreProvider;
use LastDragon_ru\LaraASP\GraphQL\Provider;
use LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Directives\TestDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Provider as TestProvider;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Override;
use PHPUnit\Framework\Attributes\CoversNothing;

use function array_merge;

/**
 * @internal
 */
#[CoversNothing]
final class AssertGraphQLSchemaEqualsTest extends TestCase {
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
        return array_merge(parent::getPackageProviders($app), [
            Provider::class,
            CoreProvider::class,
            TestProvider::class,
            LighthouseServiceProvider::class,
        ]);
    }

    /**
     * Assertion test.
     */
    public function testAssertion(): void {
        // Prepare
        $this->app()->make(DirectiveLocator::class)
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

[//]: # (end: 9396b7581da4ef186d57181c7829cb37e837a31181b78e830df4567f39492bdc)
