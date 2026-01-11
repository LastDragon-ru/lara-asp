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
use GraphQL\Utils\BreakingChangesFinder;
use GraphQL\Utils\BuildClientSchema;
use GraphQL\Utils\BuildSchema;
use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use LastDragon_ru\PhpUnit\GraphQL\GraphQLAssertions as PrinterGraphQLAssertions;
use LastDragon_ru\PhpUnit\GraphQL\GraphQLExpected;
use LastDragon_ru\PhpUnit\GraphQL\TestSettings;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Testing\TestSchemaProvider;
use PHPUnit\Framework\Assert;
use SplFileInfo;

use function assert;
use function implode;
use function ksort;
use function mb_trim;

/**
 * @phpstan-import-type Change from BreakingChangesFinder
 */
trait GraphQLAssertions {
    use PrinterGraphQLAssertions {
        assertGraphQLResult as private printerAssertGraphQLResult;
    }

    // <editor-fold desc="Abstract">
    // =========================================================================
    abstract protected function app(): Application;
    // </editor-fold>

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
    public function assertGraphQLSchemaValid(?string $message = null): void {
        // To perform validation, we should load all directives first. This is
        // required because they can be defined inside the schema (and it is
        // fine) or as a PHP class (in this case, the definition should be added
        // to the schema by hand).
        //
        // Why do not use `lighthouse:validate-schema` command? Because it loads
        // all existing directives (even not used) and thus extremely slow.

        $valid     = true;
        $message ??= 'The schema is not valid.';

        try {
            BuildSchema::build($this->getGraphQLSchemaString())->assertValid();
        } catch (Exception $exception) {
            $valid   = false;
            $message = "{$message}\n\n{$exception->getMessage()}";
        }

        Assert::assertTrue($valid, $message);
    }

    /**
     * Checks the current (application) schema has no breaking changes.
     */
    public function assertGraphQLSchemaNoBreakingChanges(
        SplFileInfo|string $expected,
        ?string $message = null,
    ): void {
        $oldSchema = BuildSchema::build(Args::content($expected));
        $newSchema = BuildSchema::build($this->getGraphQLSchemaString());
        $changes   = BreakingChangesFinder::findBreakingChanges($oldSchema, $newSchema);
        $changes   = $this->getGraphQLChanges($changes);
        $message   = ($message ?? 'The breaking changes found!')."\n\n{$changes}\n";

        Assert::assertTrue($changes === '', $message);
    }

    /**
     * Checks the current (application) schema has no dangerous changes.
     */
    public function assertGraphQLSchemaNoDangerousChanges(
        SplFileInfo|string $expected,
        ?string $message = null,
    ): void {
        $oldSchema = BuildSchema::build(Args::content($expected));
        $newSchema = BuildSchema::build($this->getGraphQLSchemaString());
        $changes   = BreakingChangesFinder::findDangerousChanges($oldSchema, $newSchema);
        $changes   = $this->getGraphQLChanges($changes);
        $message   = ($message ?? 'The dangerous changes found!')."\n\n{$changes}\n";

        Assert::assertTrue($changes === '', $message);
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

        $this->getGraphQLSchemaBuilder()->setSchema($this->app(), $provider);

        return $this;
    }

    /**
     * Resets the current (application) schema to the default schema.
     */
    protected function resetGraphQLSchema(): static {
        $this->getGraphQLSchemaBuilder()->setSchema($this->app(), null);

        return $this;
    }

    protected function getGraphQLSchema(): Schema {
        return $this->getGraphQLSchemaBuilder()->schema();
    }

    protected function getGraphQLPrinter(?Settings $settings = null): Printer {
        return $this->app()->make(Printer::class)->setSettings($settings ?? new TestSettings());
    }

    protected function getGraphQLSchemaBuilder(): SchemaBuilderWrapper {
        // Wrap
        $container = $this->app();
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

    private function getGraphQLSchemaString(): string {
        return (string) $this
            ->getGraphQLPrinter(new TestSettings())
            ->print($this->getGraphQLSchema());
    }

    /**
     * @param array<array-key, Change> $changes
     */
    private function getGraphQLChanges(array $changes): string {
        $message = '';
        $groups  = [];

        foreach ($changes as $change) {
            $groups[$change['type']] ??= [];
            $groups[$change['type']][] = $change['description'];
        }

        ksort($groups);

        foreach ($groups as $type => $descriptions) {
            $message .= "{$type}:\n\n* ".implode('* ', $descriptions)."\n\n";
        }

        return mb_trim($message);
    }
    // </editor-fold>
}
