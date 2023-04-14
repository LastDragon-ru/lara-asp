<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Filters\IntrospectionFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\IntrospectionContext;
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

    protected function getContext(?Schema $schema): Context {
        return new IntrospectionContext($this->getSettings(), $this->getDirectiveResolver(), $schema);
    }
}
