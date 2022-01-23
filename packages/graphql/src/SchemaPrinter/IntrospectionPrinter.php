<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Introspection;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\ImmutableSettings;

/**
 * Introspection schema printer.
 *
 * Following settings has no effects:
 * - {@see Settings::isPrintUnusedTypeDefinitions()}
 * - {@see Settings::isPrintDirectiveDefinitions()}
 * - {@see Settings::isPrintUnusedDirectiveDefinitions()}
 */
class IntrospectionPrinter extends Printer {
    public function setSettings(Settings $settings): static {
        return parent::setSettings(
            ImmutableSettings::createFrom($settings)
                ->setPrintUnusedTypeDefinitions(true)
                ->setPrintDirectiveDefinitions(true)
                ->setPrintUnusedDirectiveDefinitions(true)
        );
    }

    protected function isSchemaType(Type $type): bool {
        return Introspection::isIntrospectionType($type);
    }

    protected function isSchemaDirective(Directive $directive): bool {
        return Directive::isSpecifiedDirective($directive);
    }
}
