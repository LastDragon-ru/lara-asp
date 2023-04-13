<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter;

use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer\DefinitionList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as SchemaPrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions\DirectiveDefinitionNotFound;
use LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions\TypeNotFound;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\ResultImpl;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;

use function array_merge;
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
        // todo(graphql): directives in description for schema
        //      https://github.com/webonyx/graphql-php/issues/1027

        // Print
        $schema    = clone $schema;
        $settings  = $this->getSettings();
        $block     = $this->getSchemaDefinition($schema);
        $content   = $this->getDefinitionList(true);
        $content[] = $block;

        if ($settings->isPrintUnusedDefinitions()) {
            $content[] = $this->getTypeDefinitions($schema);
            $content[] = $this->getDirectiveDefinitions($schema);
        } else {
            foreach ($this->getUsedDefinitions($schema, $block) as $definition) {
                $content[] = $definition;
            }
        }

        // Return
        return new ResultImpl($content);
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
        $block     = $this->getDefinitionBlock($type);
        $list      = $this->getDefinitionList();
        $list[]    = $block;
        $content   = $this->getDefinitionList(true);
        $content[] = $list;

        foreach ($this->getUsedDefinitions($schema, $block) as $definition) {
            $content[] = $definition;
        }

        return new ResultImpl($content);
    }

    public function printType(Type $type): Result {
        $content   = $this->getDefinitionList(true);
        $content[] = $this->getDefinitionBlock($type);
        $printed   = new ResultImpl($content);

        return $printed;
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getSchemaDefinition(Schema $schema): Block {
        return $this->getDefinitionBlock($schema);
    }

    /**
     * Returns all types defined in the schema.
     *
     * @return ListBlock<Block>
     */
    protected function getTypeDefinitions(Schema $schema): ListBlock {
        $blocks = $this->getDefinitionList();

        foreach ($schema->getTypeMap() as $name => $type) {
            $blocks[$name] = $this->getDefinitionBlock($type);
        }

        return $blocks;
    }

    /**
     * Returns all directives defined in the schema.
     *
     * @return ListBlock<Block>
     */
    protected function getDirectiveDefinitions(Schema $schema): ListBlock {
        // Included?
        $blocks = $this->getDefinitionList();

        if ($this->getSettings()->isPrintDirectiveDefinitions()) {
            $directives = array_merge(
                (array) $this->getDirectiveResolver()?->getDefinitions(),
                $schema->getDirectives(),
            );

            foreach ($directives as $directive) {
                if (!isset($blocks[$directive->name])) {
                    $blocks[$directive->name] = $this->getDefinitionBlock($directive);
                }
            }
        }

        // Return
        return $blocks;
    }

    protected function getDefinitionBlock(
        Schema|Type|GraphQLDirective $definition,
    ): Block {
        return new DefinitionBlock($this->getSettings(), $this->getLevel(), $definition);
    }

    /**
     * @return ListBlock<Block>
     */
    protected function getDefinitionList(bool $root = false): ListBlock {
        return new DefinitionList($this->getSettings(), $this->getLevel(), $root);
    }

    /**
     * @return array<ListBlock<Block>>
     */
    protected function getUsedDefinitions(Schema $schema, Block $root): array {
        $directiveDefinitions = [];
        $directiveResolver    = null;
        $directives           = null;
        $types                = $this->getDefinitionList();
        $stack                = $root->getUsedDirectives() + $root->getUsedTypes();

        if ($this->getSettings()->isPrintDirectiveDefinitions()) {
            $directiveResolver = $this->getDirectiveResolver();
            $directives        = $this->getDefinitionList();

            foreach ($schema->getDirectives() as $directive) {
                $directiveDefinitions[$directive->name] = $directive;
            }
        }

        while ($stack) {
            // Added?
            $name = array_pop($stack);

            if (isset($types[$name]) || isset($directives[$name])) {
                continue;
            }

            // Add
            $block = null;

            if (str_starts_with($name, '@')) {
                if ($directives) {
                    $directive = substr($name, 1);
                    $directive = $directiveDefinitions[$directive]
                        ?? $directiveResolver?->getDefinition($directive);

                    if ($directive) {
                        $block             = $this->getDefinitionBlock($directive);
                        $directives[$name] = $block;
                    } else {
                        throw new DirectiveDefinitionNotFound($name);
                    }
                }
            } else {
                $type = $schema->getType($name);

                if ($type) {
                    $block        = $this->getDefinitionBlock($type);
                    $types[$name] = $block;
                }
            }

            // Stack
            if ($block) {
                $stack = $stack
                    + $block->getUsedDirectives()
                    + $block->getUsedTypes();
            }
        }

        return $directives
            ? [$types, $directives]
            : [$types];
    }
    // </editor-fold>
}
