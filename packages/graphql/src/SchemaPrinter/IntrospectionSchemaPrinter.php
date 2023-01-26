<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Introspection;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedSchema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\ImmutableSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\PrinterSettings;

/**
 * Introspection schema printer.
 *
 * Following settings has no effects:
 * - {@see Settings::getTypeDefinitionFilter()}
 * - {@see \LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings::getDirectiveFilter()}
 * - {@see \LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings::getDirectiveDefinitionFilter()}
 * - {@see \LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings::isPrintUnusedDefinitions()}
 * - {@see \LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings::isPrintDirectiveDefinitions()}
 */
class IntrospectionSchemaPrinter extends SchemaPrinter {
    public function setSettings(?Settings $settings): static {
        return parent::setSettings(
            ImmutableSettings::createFrom($settings ?? new DefaultSettings())
                ->setPrintUnusedDefinitions(true)
                ->setPrintDirectiveDefinitions(true)
                ->setTypeDefinitionFilter(null)
                ->setDirectiveDefinitionFilter(null)
                ->setDirectiveFilter(null),
        );
    }

    protected function getPrintedSchema(PrinterSettings $settings, Schema $schema, Block $content): PrintedSchema {
        return new PrintedIntrospectionSchemaImpl($settings->getResolver(), $schema, $content);
    }

    protected function getTypeDefinitions(PrinterSettings $settings, Schema $schema): ListBlock {
        $blocks = $this->getDefinitionList($settings);

        foreach (Introspection::getTypes() as $type) {
            $blocks[] = $this->getDefinitionBlock($settings, $type);
        }

        return $blocks;
    }

    protected function getDirectiveDefinitions(PrinterSettings $settings, Schema $schema): ListBlock {
        $blocks     = $this->getDefinitionList($settings);
        $directives = $schema->getDirectives();

        foreach ($directives as $directive) {
            if (Directive::isSpecifiedDirective($directive)) {
                $blocks[] = $this->getDefinitionBlock($settings, $directive);
            }
        }

        return $blocks;
    }
}
