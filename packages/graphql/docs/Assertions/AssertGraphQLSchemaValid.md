# `assertGraphQLSchemaValid`

Validates default internal schema (with all directives). Faster than `lighthouse:validate-schema` command because loads only used directives.

[include:example]: ./AssertGraphQLSchemaValidTest.php
[//]: # (start: preprocess/991ba34d32d6768f)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Docs\Assertions;

use LastDragon_ru\LaraASP\Core\PackageProvider as CoreProvider;
use LastDragon_ru\LaraASP\GraphQL\PackageProvider;
use LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Directives\TestDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Provider as TestProvider;
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
final class AssertGraphQLSchemaValidTest extends TestCase {
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
            PackageProvider::class,
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
            directive @a(a: Int!) on OBJECT

            type Query @a(a: 123) {
                a: String @test
            }
            GRAPHQL,
        );

        // Test
        $this->assertGraphQLSchemaValid();
    }
}
```

[//]: # (end: preprocess/991ba34d32d6768f)
