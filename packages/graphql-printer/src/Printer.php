<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter;

use ArrayAccess;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Language\AST\TypeSystemDefinitionNode;
use GraphQL\Language\AST\TypeSystemExtensionNode;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer\PrintableBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer\PrintableList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as PrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\ResultImpl;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use Override;

use function array_pop;
use function mb_substr;
use function str_starts_with;

class Printer implements PrinterContract {
    private ?DirectiveResolver $directiveResolver;
    private Settings           $settings;
    private ?Schema            $schema = null;

    public function __construct(
        ?Settings $settings = null,
        ?DirectiveResolver $directiveResolver = null,
        ?Schema $schema = null,
    ) {
        $this->setSchema($schema);
        $this->setSettings($settings);
        $this->setDirectiveResolver($directiveResolver);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    #[Override]
    public function getSettings(): Settings {
        return $this->settings;
    }

    #[Override]
    public function setSettings(?Settings $settings): static {
        $this->settings = $settings ?? new DefaultSettings();

        return $this;
    }

    #[Override]
    public function getDirectiveResolver(): ?DirectiveResolver {
        return $this->directiveResolver;
    }

    #[Override]
    public function setDirectiveResolver(?DirectiveResolver $directiveResolver): static {
        $this->directiveResolver = $directiveResolver;

        return $this;
    }

    #[Override]
    public function getSchema(): ?Schema {
        return $this->schema;
    }

    #[Override]
    public function setSchema(?Schema $schema): static {
        $this->schema = $schema;

        return $this;
    }
    // </editor-fold>

    // <editor-fold desc="Printer">
    // =========================================================================
    #[Override]
    public function print(
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema $printable,
        int $level = 0,
        int $used = 0,
        (TypeNode&Node)|Type|null $type = null,
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

    #[Override]
    public function export(
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema $printable,
        int $level = 0,
        int $used = 0,
        (TypeNode&Node)|Type|null $type = null,
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
            $content[] = $this->process($collector, $context, $content, $level, $used);
        }

        return new ResultImpl($collector, $content->serialize($collector, $level, $used));
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getContext(?Schema $schema): Context {
        return new Context($this->getSettings(), $this->getDirectiveResolver(), $schema);
    }

    protected function getBlock(
        Context $context,
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema $definition,
        (TypeNode&Node)|Type|null $type = null,
    ): Block {
        return new PrintableBlock($context, $definition, $type);
    }

    /**
     * @return Block&ArrayAccess<Block, Block>
     */
    protected function getList(Context $context, bool $root = false, bool $eof = true): Block&ArrayAccess {
        return new PrintableList($context, $root, $eof);
    }

    /**
     * @param Block&ArrayAccess<Block, Block> $root
     *
     * @return Block&ArrayAccess<Block, Block>
     */
    protected function process(
        Collector $collector,
        Context $context,
        Block&ArrayAccess $root,
        int $level,
        int $used,
    ): Block&ArrayAccess {
        $root       = $this->analyze($collector, $root, $level, $used);
        $stack      = $collector->getUsedDirectives() + $collector->getUsedTypes();
        $output     = $this->getList($context);
        $printed    = [];
        $directives = $context->getSettings()->isPrintDirectiveDefinitions();

        while ($stack !== []) {
            // Added?
            $name = array_pop($stack);

            if (isset($printed[$name])) {
                continue;
            }

            // Add
            $block = null;

            if (str_starts_with($name, '@')) {
                if ($directives) {
                    $directive = $context->getDirective(mb_substr($name, 1));

                    if ($directive !== null) {
                        $block          = $this->getBlock($context, $directive);
                        $printed[$name] = true;
                    }
                }
            } else {
                $type = $context->getType($name);

                if ($type !== null) {
                    $block          = $this->getBlock($context, $type);
                    $printed[$name] = true;
                }
            }

            // Stack
            if ($block !== null && !isset($output[$block]) && !isset($root[$block])) {
                $statistics = new Collector();
                $output[]   = $this->analyze($statistics, $block, $level, $used);
                $statistics = $collector->addUsed($statistics);
                $stack      = $stack
                    + $statistics->getUsedDirectives()
                    + $statistics->getUsedTypes();
            }
        }

        return $output;
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

    protected function isExportable(
        Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema $printable,
    ): bool {
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
