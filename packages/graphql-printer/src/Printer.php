<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter;

use ArrayAccess;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\AST\TypeSystemDefinitionNode;
use GraphQL\Language\AST\TypeSystemExtensionNode;
use GraphQL\Type\Definition\Argument as GraphQLArgument;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\EnumValueDefinition as GraphQLEnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition as GraphQLFieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer\PrintableBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer\PrintableList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as SchemaPrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions\DirectiveDefinitionNotFound;
use LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions\TypeNotFound;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\ResultImpl;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;

use function array_pop;
use function is_string;
use function str_starts_with;
use function substr;

// @phpcs:disable Generic.Files.LineLength.TooLong

class Printer implements SchemaPrinterContract {
    private ?DirectiveResolver $directiveResolver;
    private Settings           $settings;
    private int                $level;
    private ?Schema            $schema = null;

    public function __construct(
        Settings $settings = null,
        ?DirectiveResolver $directiveResolver = null,
        ?Schema $schema = null,
    ) {
        $this->setLevel(0);
        $this->setSchema($schema);
        $this->setSettings($settings);
        $this->setDirectiveResolver($directiveResolver);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    /**
     * @deprecated 4.3.0 Please see #78
     */
    public function getLevel(): int {
        return $this->level;
    }

    /**
     * @deprecated 4.3.0 Please see #78
     */
    public function setLevel(int $level): static {
        $this->level = $level;

        return $this;
    }

    public function getSettings(): Settings {
        return $this->settings;
    }

    public function setSettings(?Settings $settings): static {
        $this->settings = $settings ?? new DefaultSettings();

        return $this;
    }

    public function getDirectiveResolver(): ?DirectiveResolver {
        return $this->directiveResolver;
    }

    public function setDirectiveResolver(?DirectiveResolver $directiveResolver): static {
        $this->directiveResolver = $directiveResolver;

        return $this;
    }

    public function getSchema(): ?Schema {
        return $this->schema;
    }

    public function setSchema(?Schema $schema): static {
        $this->schema = $schema;

        return $this;
    }
    // </editor-fold>

    // <editor-fold desc="Printer">
    // =========================================================================
    /**
     * @param Node|Type|GraphQLDirective|GraphQLFieldDefinition|GraphQLArgument|GraphQLEnumValueDefinition|InputObjectField|Schema $printable
     * @param (TypeNode&Node)|Type|null                                                                                            $type
     */
    public function print(
        object $printable,
        int $level = 0,
        int $used = 0,
        TypeNode|Type|null $type = null,
    ): Result {
        // Schema?
        if ($printable instanceof Schema) {
            return $this->export($printable, $level, $used, $type);
        }

        // Print
        $collector = new Collector();
        $context   = $this->getContext($this->getSchema());
        $content   = $this->getList($context, true, false);
        $content[] = $this->getBlock($context, $printable, $type);
        $printed   = new ResultImpl($collector, $content->serialize($collector, $level, $used));

        return $printed;
    }

    /**
     * @param Node|Type|GraphQLDirective|GraphQLFieldDefinition|GraphQLArgument|GraphQLEnumValueDefinition|InputObjectField|Schema $printable
     * @param (TypeNode&Node)|Type|null                                                                                            $type
     */
    public function export(
        object $printable,
        int $level = 0,
        int $used = 0,
        TypeNode|Type|null $type = null,
    ): Result {
        // Exportable?
        if (!$this->isExportable($printable)) {
            return $this->print($printable, $level, $used, $type);
        }

        // Export
        $collector = new Collector();
        $schema    = $printable instanceof Schema ? $printable : $this->getSchema();
        $context   = $this->getContext($schema);
        $content   = $this->getList($context, true);
        $content[] = $this->getBlock($context, $printable, $type);
        $settings  = $context->getSettings();

        if ($printable instanceof Schema && $settings->isPrintUnusedDefinitions()) {
            foreach ($context->getTypes() as $definition) {
                $content[] = $this->getBlock($context, $definition);
            }

            if ($settings->isPrintDirectiveDefinitions()) {
                foreach ($context->getDirectives() as $definition) {
                    $content[] = $this->getBlock($context, $definition);
                }
            }
        } else {
            $this->process($collector, $context, $content, $level, $used);
        }

        return new ResultImpl($collector, $content->serialize($collector, $level, $used));
    }

    /**
     * @deprecated 4.3.0 Please use {@see self::print()}/{@see self::export()} instead (see #78)
     */
    public function printSchema(Schema $schema): Result {
        return $this->print($schema, $this->getLevel());
    }

    /**
     * @deprecated 4.3.0 Please use {@see self::print()}/{@see self::export()} instead (see #78)
     */
    public function printSchemaType(Schema $schema, Type|string $type): Result {
        // Type
        if (is_string($type)) {
            $name = $type;
            $type = $schema->getType($type);

            if ($type === null) {
                throw new TypeNotFound($name);
            }
        }

        // Print
        $previous = $this->getSchema();

        try {
            return $this->setSchema($schema)->export($type, $this->getLevel());
        } finally {
            $this->setSchema($previous);
        }
    }

    /**
     * @deprecated 4.3.0 Please use {@see self::print()}/{@see self::export()} instead (see #78)
     */
    public function printType(Type $type): Result {
        return $this->print($type, $this->getLevel());
    }

    /**
     * @deprecated 4.3.0 Please use {@see self::print()}/{@see self::export()} instead (see #78)
     */
    public function printNode(Node $node, ?Schema $schema = null): Result {
        $previous = $this->getSchema();

        try {
            return $this->setSchema($schema)->print($node, $this->getLevel());
        } finally {
            $this->setSchema($previous);
        }
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getContext(?Schema $schema): Context {
        return new Context($this->getSettings(), $this->getDirectiveResolver(), $schema);
    }

    /**
     * @param Node|Type|GraphQLDirective|GraphQLFieldDefinition|GraphQLArgument|GraphQLEnumValueDefinition|InputObjectField|Schema $definition
     * @param (TypeNode&Node)|Type|null                                                                                            $type
     */
    protected function getBlock(
        Context $context,
        object $definition,
        TypeNode|Type|null $type = null,
    ): Block {
        return new PrintableBlock($context, $definition, $type);
    }

    /**
     * @return Block&ArrayAccess<Block, Block>
     */
    protected function getList(Context $context, bool $root = false, bool $eof = true): Block {
        return new PrintableList($context, $root, $eof);
    }

    /**
     * @param Block&ArrayAccess<Block, Block> $root
     */
    protected function process(
        Collector $collector,
        Context $context,
        Block $root,
        int $level,
        int $used,
    ): void {
        $root       = $this->analyze($collector, $root, $level, $used);
        $stack      = $collector->getUsedDirectives() + $collector->getUsedTypes();
        $printed    = [];
        $directives = $context->getSettings()->isPrintDirectiveDefinitions();

        while ($stack) {
            // Added?
            $name = array_pop($stack);

            if (isset($printed[$name])) {
                continue;
            }

            // Add
            $block = null;

            if (str_starts_with($name, '@')) {
                if ($directives) {
                    $directive = $context->getDirective(substr($name, 1));

                    if ($directive) {
                        $block          = $this->getBlock($context, $directive);
                        $printed[$name] = true;
                    } else {
                        throw new DirectiveDefinitionNotFound($name);
                    }
                }
            } else {
                $type = $context->getType($name);

                if ($type) {
                    $block          = $this->getBlock($context, $type);
                    $printed[$name] = true;
                }
            }

            // Stack
            if ($block && !isset($root[$block])) {
                $statistics = new Collector();
                $root[]     = $this->analyze($statistics, $block, $level, $used);
                $statistics = $collector->addUsed($statistics);
                $stack      = $stack
                    + $statistics->getUsedDirectives()
                    + $statistics->getUsedTypes();
            }
        }
    }

    /**
     * @template T of Block
     *
     * @param T $block
     *
     * @return T
     */
    protected function analyze(Collector $collector, Block $block, int $level, int $used): Block {
        $block->serialize($collector, $level, $used);

        return $block;
    }

    protected function isExportable(object $printable): bool {
        $exportable = true;

        if ($printable instanceof DocumentNode) {
            foreach ($printable->definitions as $definition) {
                $exportable = $this->isExportable($definition);
                break;
            }
        } elseif ($printable instanceof Node) {
            $exportable = $printable instanceof TypeSystemDefinitionNode
                || $printable instanceof TypeSystemExtensionNode;
        } else {
            // empty
        }

        return $exportable;
    }
    // </editor-fold>
}
