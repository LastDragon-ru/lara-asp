# `assertGraphQLSchemaNoDangerousChanges`

Checks that no dangerous changes in the default internal schema (with all directives).

[include:example]: ./AssertGraphQLSchemaNoDangerousChangesTest.php
[//]: # (start: 0e4615a4114be9ae79497fb6546b573e74908c179225e635b3b8f722eb81c317)
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
final class AssertGraphQLSchemaNoDangerousChangesTest extends TestCase {
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
            ->setResolved('test', TestDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            type Query {
                a(a: Int = 123): String @test
            }
            GRAPHQL,
        );

        // Test
        $this->assertGraphQLSchemaNoDangerousChanges(
            <<<'GRAPHQL'
            directive @test on FIELD_DEFINITION

            type Query {
                a(a: Int = 123): String @test
            }
            GRAPHQL,
        );
    }
}
```

[//]: # (end: 0e4615a4114be9ae79497fb6546b573e74908c179225e635b3b8f722eb81c317)
