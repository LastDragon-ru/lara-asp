# `assertGraphQLSchemaValid`

Validates default internal schema (with all directives). Faster than `lighthouse:validate-schema` command because loads only used directives.

[include:example]: ./AssertGraphQLSchemaValidTest.php
[//]: # (start: 1ea7c7a03ef9aacec28855402c5dc9a0417ce9d201dcdd26ad946d25f7dd56f0)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace Assertions;

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

[//]: # (end: 1ea7c7a03ef9aacec28855402c5dc9a0417ce9d201dcdd26ad946d25f7dd56f0)
