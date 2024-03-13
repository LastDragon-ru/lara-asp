<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use Closure;
use Exception;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Introspection;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildClientSchema;
use GraphQL\Utils\BuildSchema;
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
     * Compares GraphQL schema with current (application) public (client) schema.
     */
    public function assertGraphQLIntrospectionEquals(
        GraphQLExpected|SplFileInfo|string $expected,
        string $message = '',
    ): void {
        // Schema
        $schema = $this->getGraphQLSchema();
        $schema = Introspection::fromSchema($schema);
        $schema = BuildClientSchema::build($schema);

        if (!($expected instanceof GraphQLExpected)) {
            $expected = (new GraphQLExpected($expected))->setSchema($schema);
        }

        // Settings
        if ($expected->getSettings() === null) {
            $filter   = static fn () => true;
            $expected = $expected->setSettings(
                (new TestSettings())
                    ->setPrintUnusedDefinitions(true)
                    ->setTypeDefinitionFilter($filter)
                    ->setDirectiveDefinitionFilter($filter),
            );
        }

        // Assert
        $this->assertGraphQLPrintableEquals($expected, $schema, $message);
    }

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
     * Validates current (application) schema.
     */
    public function assertGraphQLSchemaValid(string $message = ''): void {
        // To perform validation, we should load all directives first. This is
        // required because they can be defined inside the schema (and it is
        // fine) or as a PHP class (in this case, the definition should be added
        // to the schema by hand).
        //
        // Why do not use `lighthouse:validate-schema` command? Because it loads
        // all existing directives (even not used) and thus extremely slow.

        // Print
        $schema = $this->getGraphQLSchema();
        $schema = (string) $this
            ->getGraphQLPrinter(new TestSettings())
            ->print($schema);

        // Validate
        $valid = true;

        try {
            BuildSchema::build($schema)->assertValid();
        } catch (Exception $exception) {
            $valid   = false;
            $message = ($message ?: 'The schema is not valid.')."\n\n".$exception->getMessage();
        }

        self::assertTrue($valid, $message);
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
