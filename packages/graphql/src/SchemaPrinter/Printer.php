<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer\DefinitionList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;

use function end;
use function explode;
use function ltrim;

class Printer {
    protected Settings $settings;
    protected int      $level = 0;

    public function __construct(
        protected DirectiveResolver $directives,
        Settings $settings = null,
    ) {
        $this->settings = $settings ?? new DefaultSettings();
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

    public function setSettings(Settings $settings): static {
        $this->settings = $settings;

        return $this;
    }

    public function print(Schema $schema): PrintedSchema {
        // Collect
        $usedTypes        = [];
        $usedDirectives   = [];
        $schemaBlock      = $this->getSchema($schema, $usedTypes, $usedDirectives);
        $typesBlocks      = $this->getSchemaTypes($schema, $usedTypes, $usedDirectives);
        $directivesBlocks = $this->getSchemaDirectives($schema, $usedDirectives, $usedTypes);

        // Print
        $settings  = $this->getSettings();
        $content   = new DefinitionList($this->getSettings(), $this->getLevel(), true);
        $content[] = $schemaBlock;
        $content[] = $this->getDefinitionList(
            $typesBlocks,
            $usedTypes,
            $settings->isPrintUnusedDefinitions(),
        );
        $content[] = $this->getDefinitionList(
            $directivesBlocks,
            $usedDirectives,
            $settings->isPrintUnusedDefinitions(),
        );

        // todo(graphql): directives in description for schema
        //      https://github.com/webonyx/graphql-php/issues/1027

        // Return
        return new PrintedSchema((string) $content);
    }

    /**
     * @param array<string,string> $usedTypes
     * @param array<string,string> $usedDirectives
     */
    protected function getSchema(
        Schema $schema,
        array &$usedTypes = [],
        array &$usedDirectives = [],
    ): DefinitionBlock {
        return $this->getDefinitionBlock($schema, $usedTypes, $usedDirectives);
    }

    /**
     * @param array<string,string> $usedTypes
     * @param array<string,string> $usedDirectives
     *
     * @return array<DefinitionBlock>
     */
    protected function getSchemaTypes(Schema $schema, array &$usedTypes = [], array &$usedDirectives = []): array {
        $blocks = [];

        foreach ($schema->getTypeMap() as $type) {
            // Standard?
            if (!$this->isSchemaType($type)) {
                continue;
            }

            // Nope
            $blocks[] = $this->getDefinitionBlock($type, $usedTypes, $usedDirectives);
        }

        return $blocks;
    }

    protected function isSchemaType(Type $type): bool {
        return !Type::isBuiltInType($type);
    }

    /**
     * @param array<string,string> $usedTypes
     * @param array<string,string> $directives
     *
     * @return array<Block>
     */
    protected function getSchemaDirectives(Schema $schema, array $directives, array &$usedTypes = []): array {
        // Included?
        $blocks   = [];
        $settings = $this->getSettings();
        $included = $settings->isPrintDirectiveDefinitions();

        if (!$included) {
            return $blocks;
        }

        // Add directives from Schema
        $processed = [];

        foreach ($schema->getDirectives() as $directive) {
            // Mark
            $processed[$directive->name] = true;

            // Standard?
            if (!$this->isSchemaDirective($directive)) {
                continue;
            }

            // Nope
            $blocks[] = $this->getDefinitionBlock($directive, $usedTypes);
        }

        // Add Lighthouse directives
        foreach ($directives as $directive) {
            // Processed?
            if (isset($processed[$directive])) {
                continue;
            }

            // Nope
            $blocks[] = $this->getDefinitionBlock(
                $this->directives->get($directive),
                $usedTypes,
            );
        }

        // Return
        return $blocks;
    }

    protected function isSchemaDirective(Directive $directive): bool {
        return !Directive::isSpecifiedDirective($directive);
    }

    /**
     * @param array<string,string> $usedTypes
     * @param array<string,string> $usedDirectives
     */
    protected function getDefinitionBlock(
        Schema|Type|Directive $definition,
        array &$usedTypes = [],
        array &$usedDirectives = [],
    ): DefinitionBlock {
        $block          = new DefinitionBlock($this->getSettings(), $this->getLevel(), $definition);
        $usedTypes      += $block->getUsedTypes();
        $usedDirectives += $block->getUsedDirectives();

        return $block;
    }

    /**
     * @param array<Block>         $blocks
     * @param array<string,string> $used
     */
    protected function getDefinitionList(
        array $blocks,
        array $used,
        bool $includeUnused,
    ): BlockList {
        $list = new DefinitionList($this->getSettings(), $this->getLevel());

        foreach ($blocks as $block) {
            if ($includeUnused || $this->getDefinitionListIsUsed($block, $used)) {
                $list[] = $block;
            }
        }

        return $list;
    }

    /**
     * @param array<string,string> $used
     */
    private function getDefinitionListIsUsed(Block $block, array $used): bool {
        // Block names may have a "type name" prefix eg "type A", "interface B",
        // etc, meanwhile `$used` doesn't have it. So we should remove the
        // prefix before checking.
        $name = $block instanceof Named ? $block->getName() : '';
        $name = explode(' ', $name);
        $name = ltrim((string) end($name), '@');

        return isset($used[$name]);
    }
}
