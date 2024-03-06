<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use Closure;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLAssertions as PrinterGraphQLAssertions;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\GraphQLExpected;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\TestSettings;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Testing\MocksResolvers;
use Nuwave\Lighthouse\Testing\TestSchemaProvider;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function assert;

// @phpcs:disable Generic.Files.LineLength.TooLong

/**
 * @mixin TestCase
 */
trait GraphQLAssertions {
    use MocksResolvers;
    use PrinterGraphQLAssertions {
        assertGraphQLResult as private printerAssertGraphQLResult;
    }

    // <editor-fold desc="Assertions">
    // =========================================================================
    /**
     * Compares GraphQL schema with current (application) schema.
     */
    public function assertGraphQLSchemaEquals(
        GraphQLExpected|SplFileInfo|string $expected,
        string $message = '',
    ): void {
        $this->assertGraphQLPrintableEquals(
            $expected,
            $this->getGraphQLSchema(),
            $message,
        );
    }

    /**
     * @param Closure(Printer, Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema): Result $print
     */
    private function assertGraphQLResult(
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|GraphQLExpected|SplFileInfo|string $expected,
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|Result|SplFileInfo|string $actual,
        string $message,
        Closure $print,
    ): Result {
        if (!($expected instanceof GraphQLExpected)) {
            $expected = (new GraphQLExpected($expected))->setSchema($this->getGraphQLSchema());
        }

        return $this->printerAssertGraphQLResult($expected, $actual, $message, $print);
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * Sets the current (application) schema to the given.
     */
    protected function useGraphQLSchema(Schema|DocumentNode|DocumentAST|SplFileInfo|string $schema): static {
        if ($schema instanceof DocumentAST) {
            // We just need all definitions
            $schema = new DocumentNode([
                'definitions' => (new NodeList([]))
                    ->merge($schema->types)
                    ->merge($schema->typeExtensions)
                    ->merge($schema->directives),
            ]);
        }

        $schema   = $schema instanceof Schema || $schema instanceof DocumentNode
            ? (string) $this->getGraphQLPrinter()->print($schema)
            : Args::content($schema);
        $provider = new TestSchemaProvider($schema);

        $this->getGraphQLSchemaBuilder()->setSchema($provider);

        return $this;
    }

    /**
     * Resets the current (application) schema to the default schema.
     */
    protected function resetGraphQLSchema(): static {
        $this->getGraphQLSchemaBuilder()->setSchema(null);

        return $this;
    }

    protected function getGraphQLSchema(): Schema {
        return $this->getGraphQLSchemaBuilder()->schema();
    }

    protected function getGraphQLPrinter(Settings $settings = null): Printer {
        return Container::getInstance()->make(Printer::class)->setSettings($settings ?? new TestSettings());
    }

    protected function getGraphQLSchemaBuilder(): SchemaBuilderWrapper {
        // Wrap
        $container = Container::getInstance();
        $builder   = $container->resolved(SchemaBuilder::class)
            ? $container->make(SchemaBuilder::class)
            : null;

        if (!($builder instanceof SchemaBuilderWrapper)) {
            $container->extend(
                SchemaBuilder::class,
                static function (SchemaBuilder $builder): SchemaBuilder {
                    return new SchemaBuilderWrapper($builder);
                },
            );
        }

        // Instance
        $builder = $container->make(SchemaBuilder::class);

        assert($builder instanceof SchemaBuilderWrapper);

        // Return
        return $builder;
    }
    // </editor-fold>
}
