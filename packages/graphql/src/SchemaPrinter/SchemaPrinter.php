<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedSchema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedType;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Exceptions\TypeNotFound;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer\DefinitionList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer as SchemaPrinterContract;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\ExecutableTypeNodeConverter;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_pop;
use function is_string;
use function str_starts_with;
use function substr;

class SchemaPrinter implements SchemaPrinterContract {
    protected Settings $settings;
    protected int      $level = 0;

    public function __construct(
        protected TypeRegistry $registry,
        protected DirectiveLocator $locator,
        protected ExecutableTypeNodeConverter $converter,
        Settings $settings = null,
    ) {
        $this->setSettings($settings);
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
    // </editor-fold>

    // <editor-fold desc="Printer">
    // =========================================================================
    public function printSchema(Schema $schema): PrintedSchema {
        // todo(graphql): directives in description for schema
        //      https://github.com/webonyx/graphql-php/issues/1027

        // Print
        $schema    = clone $schema;
        $resolver  = $this->getDirectiveResolver($schema);
        $settings  = $this->getSettings();
        $block     = $this->getSchemaDefinition($schema);
        $content   = $this->getDefinitionList(true);
        $content[] = $block;

        if ($settings->isPrintUnusedDefinitions()) {
            $content[] = $this->getTypeDefinitions($schema);
            $content[] = $this->getDirectiveDefinitions($resolver, $schema);
        } else {
            foreach ($this->getUsedDefinitions($resolver, $schema, $block) as $definition) {
                $content[] = $definition;
            }
        }

        // Return
        return $this->getPrintedSchema($resolver, $schema, $content);
    }

    public function printSchemaType(Schema $schema, Type|string $type): PrintedType {
        // Type
        if (is_string($type)) {
            $name = $type;
            $type = $schema->getType($type);

            if ($type === null) {
                throw new TypeNotFound($name);
            }
        }

        // Print
        $resolver  = $this->getDirectiveResolver($schema);
        $block     = $this->getDefinitionBlock($type);
        $list      = $this->getDefinitionList();
        $list[]    = $block;
        $content   = $this->getDefinitionList(true);
        $content[] = $list;

        foreach ($this->getUsedDefinitions($resolver, $schema, $block) as $definition) {
            $content[] = $definition;
        }

        return new PrintedTypeImpl($content);
    }

    public function printType(Type $type): PrintedType {
        $content   = $this->getDefinitionList(true);
        $content[] = $this->getDefinitionBlock($type);
        $printed   = new PrintedTypeImpl($content);

        return $printed;
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    protected function getPrintedSchema(DirectiveResolver $resolver, Schema $schema, Block $content): PrintedSchema {
        return new PrintedSchemaImpl($resolver, $schema, $content);
    }

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

        foreach ($schema->getTypeMap() as $type) {
            if ($blocks->isTypeDefinitionAllowed($type)) {
                $blocks[] = $this->getDefinitionBlock($type);
            }
        }

        return $blocks;
    }

    /**
     * Returns all directives defined in the schema.
     *
     * @return ListBlock<Block>
     */
    protected function getDirectiveDefinitions(DirectiveResolver $resolver, Schema $schema): ListBlock {
        // Included?
        $blocks = $this->getDefinitionList();

        if ($this->getSettings()->isPrintDirectiveDefinitions()) {
            $directives = $resolver->getDefinitions();

            foreach ($directives as $directive) {
                if ($blocks->isDirectiveDefinitionAllowed($directive->name)) {
                    $blocks[] = $this->getDefinitionBlock($directive);
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
    protected function getUsedDefinitions(DirectiveResolver $resolver, Schema $schema, Block $root): array {
        $directives = $this->getDefinitionList();
        $types      = $this->getDefinitionList();
        $stack      = $root->getUsedDirectives() + $root->getUsedTypes();

        while ($stack) {
            // Added?
            $name = array_pop($stack);

            if (isset($types[$name]) || isset($directives[$name])) {
                continue;
            }

            // Add
            $block = null;

            if (str_starts_with($name, '@')) {
                $directive = $resolver->getDefinition(substr($name, 1));
                $printable = $root->isDirectiveDefinitionAllowed($directive->name);

                if ($printable) {
                    $block             = $this->getDefinitionBlock($directive);
                    $directives[$name] = $block;
                }
            } else {
                $type = $schema->getType($name);

                if ($type && $root->isTypeDefinitionAllowed($type)) {
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

        return [
            $types,
            $directives,
        ];
    }

    private function getDirectiveResolver(Schema $schema): DirectiveResolver {
        return new DirectiveResolver($this->registry, $this->locator, $this->converter, $schema->getDirectives());
    }
    // </editor-fold>
}
