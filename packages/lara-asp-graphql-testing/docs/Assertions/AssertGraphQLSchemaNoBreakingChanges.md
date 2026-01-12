# `assertGraphQLSchemaNoBreakingChanges`

Checks that no breaking changes in the default internal schema (with all directives).

[include:example]: ./AssertGraphQLSchemaNoBreakingChangesTest.php
[//]: # (start: preprocess/f6f137ef61ef41f2)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Docs\Assertions;

use LastDragon_ru\LaraASP\Core\PackageProvider as CorePackageProvider;
use LastDragon_ru\LaraASP\GraphQL\PackageProvider as GraphQLPackageProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Assertions;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Provider as TestProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestDirective;
use LastDragon_ru\LaraASP\Testing\Testing\TestCase;
use Nuwave\Lighthouse\LighthouseServiceProvider;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Override;
use PHPUnit\Framework\Attributes\CoversNothing;

use function array_merge;

/**
 * @internal
 */
#[CoversNothing]
final class AssertGraphQLSchemaNoBreakingChangesTest extends TestCase {
    /**
     * Trait where assertion defined.
     */
    use Assertions;

    /**
     * Preparation for test.
     *
     * @inheritDoc
     */
    #[Override]
    protected function getPackageProviders(mixed $app): array {
        return array_merge(parent::getPackageProviders($app), [
            TestProvider::class,
            CorePackageProvider::class,
            GraphQLPackageProvider::class,
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
                a: String @test
                b: Int! @test
            }
            GRAPHQL,
        );

        // Test
        $this->assertGraphQLSchemaNoBreakingChanges(
            <<<'GRAPHQL'
            directive @test on FIELD_DEFINITION

            type Query {
                a: String @test
            }
            GRAPHQL,
        );
    }
}
```

[//]: # (end: preprocess/f6f137ef61ef41f2)
