# `assertGraphQLSchemaNoDangerousChanges`

Checks that no dangerous changes in the default internal schema (with all directives).

[include:example]: ./AssertGraphQLSchemaNoDangerousChangesTest.php
[//]: # (start: preprocess/afffe5cc30a9637b)
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
final class AssertGraphQLSchemaNoDangerousChangesTest extends TestCase {
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

[//]: # (end: preprocess/afffe5cc30a9637b)
