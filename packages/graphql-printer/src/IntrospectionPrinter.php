<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter;

use GraphQL\Type\Schema;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\GraphQLPrinter\Filters\IntrospectionFilter;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Misc\IntrospectionContext;
use LastDragon_ru\GraphQLPrinter\Settings\DefaultSettings;
use LastDragon_ru\GraphQLPrinter\Settings\ImmutableSettings;
use Override;

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
    #[Override]
    public function setSettings(?Settings $settings): static {
        $settings ??= new DefaultSettings();
        $filter     = new IntrospectionFilter();

        return parent::setSettings(
            ImmutableSettings::createFrom($settings)
                ->setPrintUnusedDefinitions(true)
                ->setPrintDirectiveDefinitions(true)
                ->setTypeDefinitionFilter($filter)
                ->setTypeFilter($filter)
                ->setDirectiveDefinitionFilter($filter)
                ->setDirectiveFilter($filter),
        );
    }

    #[Override]
    protected function getContext(?Schema $schema): Context {
        return new IntrospectionContext($this->getSettings(), $this->getDirectiveResolver(), $schema);
    }
}
