<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Introspection;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\ImmutableSettings;

/**
 * Introspection schema printer.
 *
 * Following settings has no effects:
 * - {@see Settings::getTypeFilter()}
 * - {@see Settings::getTypeDefinitionFilter()}
 * - {@see Settings::getDirectiveFilter}
 * - {@see Settings::getDirectiveDefinitionFilter}
 * - {@see Settings::isPrintUnusedDefinitions}
 * - {@see Settings::isPrintDirectiveDefinitions}
 */
class IntrospectionPrinter extends Printer {
    public function setSettings(?Settings $settings): static {
        return parent::setSettings(
            ImmutableSettings::createFrom($settings ?? new DefaultSettings())
                ->setPrintUnusedDefinitions(true)
                ->setPrintDirectiveDefinitions(true)
                ->setTypeDefinitionFilter(null)
                ->setTypeFilter(null)
                ->setDirectiveDefinitionFilter(null)
                ->setDirectiveFilter(null),
        );
    }

    protected function getTypeDefinitions(Schema $schema): ListBlock {
        $blocks = $this->getDefinitionList();

        foreach (Introspection::getTypes() as $type) {
            $blocks[] = $this->getDefinitionBlock($type);
        }

        return $blocks;
    }

    protected function getDirectiveDefinitions(Schema $schema): ListBlock {
        $blocks     = $this->getDefinitionList();
        $directives = $schema->getDirectives();

        foreach ($directives as $directive) {
            if (Directive::isSpecifiedDirective($directive)) {
                $blocks[] = $this->getDefinitionBlock($directive);
            }
        }

        return $blocks;
    }
}
