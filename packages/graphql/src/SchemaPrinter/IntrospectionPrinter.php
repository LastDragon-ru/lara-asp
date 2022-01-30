<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Introspection;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Printer\DefinitionList;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\ImmutableSettings;

/**
 * Introspection schema printer.
 *
 * Following settings has no effects:
 * - {@see Settings::isPrintUnusedDefinitions()}
 * - {@see Settings::isPrintDirectiveDefinitions()}
 */
class IntrospectionPrinter extends Printer {
    public function setSettings(Settings $settings): static {
        return parent::setSettings(
            ImmutableSettings::createFrom($settings)
                ->setPrintUnusedDefinitions(true)
                ->setPrintDirectiveDefinitions(true),
        );
    }

    protected function getTypeDefinitions(PrinterSettings $settings, Schema $schema): DefinitionList {
        $blocks = $this->getDefinitionList($settings);

        foreach (Introspection::getTypes() as $type) {
            $blocks[] = $this->getDefinitionBlock($settings, $type);
        }

        return $blocks;
    }

    protected function getDirectiveDefinitions(PrinterSettings $settings, Schema $schema): DefinitionList {
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
