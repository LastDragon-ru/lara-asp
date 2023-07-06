<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
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

class Printer implements SchemaPrinterContract {
    protected ?DirectiveResolver $directiveResolver;
    protected Settings           $settings;
    protected int                $level;

    public function __construct(Settings $settings = null, ?DirectiveResolver $directiveResolver = null) {
        $this->setLevel(0);
        $this->setSettings($settings);
        $this->setDirectiveResolver($directiveResolver);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getLevel(): int {
        return $this->level;
    }

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
    // </editor-fold>

    // <editor-fold desc="Printer">
    // =========================================================================
    public function printSchema(Schema $schema): Result {
        // Print
        $collector = new Collector();
        $context   = $this->getContext($schema);
        $level     = $this->getLevel();
        $block     = $this->getDefinitionBlock($context, $schema);
        $content   = $this->getDefinitionList($context, true);
        $content[] = $block;

        if ($context->getSettings()->isPrintUnusedDefinitions()) {
            $content[] = $this->getTypeDefinitions($context);
            $content[] = $this->getDirectiveDefinitions($context);
        } else {
            foreach ($this->getUsedDefinitions($collector, $context, $block, $level) as $definition) {
                $content[] = $definition;
            }
        }

        // Return
        return new ResultImpl($collector, $content->serialize($collector, $level, 0));
    }

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
        $collector = new Collector();
        $context   = $this->getContext($schema);
        $level     = $this->getLevel();
        $block     = $this->getDefinitionBlock($context, $type);
        $list      = $this->getDefinitionList($context);
        $list[]    = $block;
        $content   = $this->getDefinitionList($context, true);
        $content[] = $list;

        foreach ($this->getUsedDefinitions($collector, $context, $block, $level) as $definition) {
            $content[] = $definition;
        }

        return new ResultImpl($collector, $content->serialize($collector, $level, 0));
    }

    public function printType(Type $type): Result {
        $collector = new Collector();
        $context   = $this->getContext(null);
        $content   = $this->getDefinitionList($context, true);
        $content[] = $this->getDefinitionBlock($context, $type);
        $printed   = new ResultImpl($collector, $content->serialize($collector, $this->getLevel(), 0));

        return $printed;
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getContext(?Schema $schema): Context {
        return new Context($this->getSettings(), $this->getDirectiveResolver(), $schema);
    }

    /**
     * Returns all types defined in the schema.
     *
     * @return ListBlock<Block>
     */
    protected function getTypeDefinitions(Context $context): ListBlock {
        $blocks = $this->getDefinitionList($context);

        foreach ($context->getTypes() as $type) {
            if (!isset($blocks[$type->name()])) {
                $blocks[$type->name()] = $this->getDefinitionBlock($context, $type);
            }
        }

        return $blocks;
    }

    /**
     * Returns all directives defined in the schema.
     *
     * @return ListBlock<Block>
     */
    protected function getDirectiveDefinitions(Context $context): ListBlock {
        // Included?
        $blocks = $this->getDefinitionList($context);

        if ($context->getSettings()->isPrintDirectiveDefinitions()) {
            foreach ($context->getDirectives() as $directive) {
                $name = $directive instanceof DirectiveDefinitionNode
                    ? $directive->name->value
                    : $directive->name;

                if (!isset($blocks[$name])) {
                    $blocks[$name] = $this->getDefinitionBlock($context, $directive);
                }
            }
        }

        // Return
        return $blocks;
    }

    protected function getDefinitionBlock(
        Context $context,
        Schema|Type|GraphQLDirective|Node $definition,
    ): Block {
        return new PrintableBlock($context, $definition);
    }

    /**
     * @return ListBlock<Block>
     */
    protected function getDefinitionList(Context $context, bool $root = false): ListBlock {
        return new PrintableList($context, $root);
    }

    /**
     * @param int $level *
     *
     * @return array<ListBlock<Block>>
     */
    protected function getUsedDefinitions(Collector $collector, Context $context, Block $root, int $level): array {
        $root       = $this->analyze($collector, $level, $root);
        $types      = $this->getDefinitionList($context);
        $stack      = $collector->getUsedDirectives() + $collector->getUsedTypes();
        $directives = $context->getSettings()->isPrintDirectiveDefinitions()
            ? $this->analyze($collector, $level, $this->getDefinitionList($context))
            : null;

        while ($stack) {
            // Added?
            $name = array_pop($stack);

            if (isset($types[$name]) || isset($directives[$name])) {
                continue;
            }

            // Add
            $block = null;

            if (str_starts_with($name, '@')) {
                if ($directives !== null) {
                    $directive = $context->getDirective(substr($name, 1));

                    if ($directive) {
                        $block             = $this->getDefinitionBlock($context, $directive);
                        $directives[$name] = $this->analyze($collector, $level, $block);
                    } else {
                        throw new DirectiveDefinitionNotFound($name);
                    }
                }
            } else {
                $type = $context->getType($name);

                if ($type) {
                    $block        = $this->getDefinitionBlock($context, $type);
                    $types[$name] = $this->analyze($collector, $level, $block);
                }
            }

            // Stack
            if ($block) {
                $stack = $stack
                    + $collector->getUsedDirectives()
                    + $collector->getUsedTypes();
            }
        }

        return $directives
            ? [$types, $directives]
            : [$types];
    }

    /**
     * @template T of Block
     *
     * @param T $block
     *
     * @return T
     */
    private function analyze(Collector $collector, int $level, Block $block): Block {
        $block->serialize($collector, $level, 0);

        return $block;
    }
    // </editor-fold>
}
