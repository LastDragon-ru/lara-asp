<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer\DefinitionList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\ExecutableTypeNodeConverter;

class Printer {
    protected Settings $settings;
    protected int      $level = 0;

    public function __construct(
        protected DirectiveLocator $locator,
        protected ExecutableTypeNodeConverter $converter,
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
        $resolver         = new DirectiveResolver($this->locator, $this->converter, $schema->getDirectives());
        $settings         = new PrinterSettings($resolver, $this->getSettings());
        $schemaBlock      = $this->getSchema($settings, $schema);
        $typesBlocks      = $this->getSchemaTypes($settings, $schema);
        $directivesBlocks = $this->getSchemaDirectives($settings, $schema);

        // Print
        $content   = new DefinitionList($settings, $this->getLevel(), true);
        $content[] = $schemaBlock;
        $content[] = $typesBlocks;
        $content[] = $directivesBlocks;

        // todo(graphql): directives in description for schema
        //      https://github.com/webonyx/graphql-php/issues/1027

        // Return
        return new PrintedSchema((string) $content);
    }

    protected function getSchema(PrinterSettings $settings, Schema $schema): DefinitionBlock {
        return $this->getDefinitionBlock($settings, $schema);
    }

    /**
     * Returns all types defined in the schema.
     *
     * @return DefinitionList<DefinitionBlock>
     */
    protected function getSchemaTypes(PrinterSettings $settings, Schema $schema): DefinitionList {
        $blocks = $this->getDefinitionList($settings);

        foreach ($schema->getTypeMap() as $type) {
            // Standard?
            if (Type::isBuiltInType($type)) {
                continue;
            }

            // Nope
            $blocks[] = $this->getDefinitionBlock($settings, $type);
        }

        return $blocks;
    }

    /**
     * Returns all directives defined in the schema.
     *
     * @return DefinitionList<DefinitionBlock>
     */
    protected function getSchemaDirectives(PrinterSettings $settings, Schema $schema): DefinitionList {
        // Included?
        $blocks = $this->getDefinitionList($settings);

        if ($settings->isPrintDirectiveDefinitions()) {
            $filter     = $settings->getDirectiveFilter();
            $directives = $settings->getResolver()->getDefinitions();

            foreach ($directives as $directive) {
                // Introspection?
                if (Directive::isSpecifiedDirective($directive)) {
                    continue;
                }

                // Not allowed?
                if ($filter !== null && !$filter->isAllowedDirective($directive)) {
                    continue;
                }

                // Nope
                $blocks[] = $this->getDefinitionBlock($settings, $directive);
            }
        }

        // Return
        return $blocks;
    }

    protected function getDefinitionList(PrinterSettings $settings): DefinitionList {
        return new DefinitionList($settings, $this->getLevel());
    }

    protected function getDefinitionBlock(
        PrinterSettings $settings,
        Schema|Type|Directive $definition
    ): DefinitionBlock {
        return new DefinitionBlock($settings, $this->getLevel(), $definition);
    }
}
