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
final class AssertGraphQLSchemaNoBreakingChangesTest extends TestCase {
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
