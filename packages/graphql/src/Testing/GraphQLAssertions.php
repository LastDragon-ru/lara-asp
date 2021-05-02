<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Schema;
use GraphQL\Utils\SchemaPrinter;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Nuwave\Lighthouse\GraphQL;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Nuwave\Lighthouse\Testing\MocksResolvers;
use Nuwave\Lighthouse\Testing\TestSchemaProvider;
use SplFileInfo;

/**
 * @mixin \PHPUnit\Framework\TestCase
 */
trait GraphQLAssertions {
    use MocksResolvers;

    /**
     * Compares two GraphQL schemas.
     */
    public function assertGraphQLSchemaEquals(
        SplFileInfo|string $expected,
        SplFileInfo|string $schema,
        string $message = '',
    ): void {
        $this->assertEquals(
            Args::content($expected),
            $this->serializeGraphQLSchema($schema),
            $message,
        );
    }

    protected function useGraphQLSchema(SplFileInfo|string $schema): static {
        $schema = Args::content($schema);

        $this->app->bind(SchemaSourceProvider::class, static function () use ($schema): SchemaSourceProvider {
            return new TestSchemaProvider($schema);
        });

        return $this;
    }

    protected function getGraphQLSchema(SplFileInfo|string $schema): Schema {
        $this->useGraphQLSchema($schema);

        $graphql = $this->app->make(GraphQL::class);
        $schema  = $graphql->prepSchema();

        return $schema;
    }

    protected function serializeGraphQLSchema(SplFileInfo|string $schema): string {
        return SchemaPrinter::doPrint($this->getGraphQLSchema($schema));
    }
}
