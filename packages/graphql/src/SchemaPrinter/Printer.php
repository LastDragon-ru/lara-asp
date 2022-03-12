<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer\DefinitionList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedSchema as PrintedSchemaContract;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Printer as PrinterContract;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\ExecutableTypeNodeConverter;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_pop;
use function str_starts_with;
use function substr;

class Printer implements PrinterContract {
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

    public function print(Schema $schema): PrintedSchemaContract {
        // todo(graphql): directives in description for schema
        //      https://github.com/webonyx/graphql-php/issues/1027

        // Print
        $schema    = clone $schema;
        $resolver  = new DirectiveResolver($this->registry, $this->locator, $this->converter, $schema->getDirectives());
        $settings  = new PrinterSettings($resolver, $this->getSettings());
        $block     = $this->getSchemaDefinition($settings, $schema);
        $content   = $this->getDefinitionList($settings, true);
        $content[] = $block;

        if ($settings->isPrintUnusedDefinitions()) {
            $content[] = $this->getTypeDefinitions($settings, $schema);
            $content[] = $this->getDirectiveDefinitions($settings, $schema);
        } else {
            foreach ($this->getUsedDefinitions($settings, $schema, $block) as $definition) {
                $content[] = $definition;
            }
        }

        // Return
        return new PrintedSchema($schema, $content);
    }

    protected function getSchemaDefinition(PrinterSettings $settings, Schema $schema): Block {
        return $this->getDefinitionBlock($settings, $schema);
    }

    /**
     * Returns all types defined in the schema.
     *
     * @return BlockList<Block>
     */
    protected function getTypeDefinitions(PrinterSettings $settings, Schema $schema): BlockList {
        $blocks = $this->getDefinitionList($settings);

        foreach ($schema->getTypeMap() as $type) {
            if ($settings->isTypeDefinitionAllowed($type)) {
                $blocks[] = $this->getDefinitionBlock($settings, $type);
            }
        }

        return $blocks;
    }

    /**
     * Returns all directives defined in the schema.
     *
     * @return BlockList<Block>
     */
    protected function getDirectiveDefinitions(PrinterSettings $settings, Schema $schema): BlockList {
        // Included?
        $blocks = $this->getDefinitionList($settings);

        if ($settings->isPrintDirectiveDefinitions()) {
            $directives = $settings->getResolver()->getDefinitions();

            foreach ($directives as $directive) {
                if ($settings->isDirectiveDefinitionAllowed($directive->name)) {
                    $blocks[] = $this->getDefinitionBlock($settings, $directive);
                }
            }
        }

        // Return
        return $blocks;
    }

    /**
     * @return array<BlockList>
     */
    protected function getUsedDefinitions(PrinterSettings $settings, Schema $schema, Block $root): array {
        $directivesDefinitions = $settings->isPrintDirectiveDefinitions();
        $directivesResolver    = $settings->getResolver();
        $directives            = $this->getDefinitionList($settings);
        $types                 = $this->getDefinitionList($settings);
        $stack                 = $root->getUsedDirectives() + $root->getUsedTypes();

        while ($stack) {
            // Added?
            $name = array_pop($stack);

            if (isset($types[$name]) || isset($directives[$name])) {
                continue;
            }

            // Add
            $block = null;

            if (str_starts_with($name, '@')) {
                if ($directivesDefinitions) {
                    $directive = $directivesResolver->getDefinition(substr($name, 1));
                    $printable = $settings->isDirectiveDefinitionAllowed($directive->name);

                    if ($printable) {
                        $block             = $this->getDefinitionBlock($settings, $directive);
                        $directives[$name] = $block;
                    }
                }
            } else {
                $type = $schema->getType($name);

                if ($type && $settings->isTypeDefinitionAllowed($type)) {
                    $block        = $this->getDefinitionBlock($settings, $type);
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

    protected function getDefinitionList(PrinterSettings $settings, bool $schema = false): BlockList {
        return new DefinitionList($settings, $this->getLevel(), $schema);
    }

    protected function getDefinitionBlock(
        PrinterSettings $settings,
        Schema|Type|GraphQLDirective $definition,
    ): Block {
        return new DefinitionBlock($settings, $this->getLevel(), $definition);
    }
}
