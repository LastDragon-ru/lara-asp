<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedType;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\SchemaTypePrinter as PartialPrinterContract;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Exceptions\TypeNotFound;

use function is_string;

class SchemaTypePrinter extends Printer implements PartialPrinterContract {
    public function print(Schema $schema, Type|string $type): PrintedType {
        // Type
        if (is_string($type)) {
            $name = $type;
            $type = $schema->getType($type);

            if ($type === null) {
                throw new TypeNotFound($name);
            }
        }

        // Print
        $settings  = $this->getPrinterSettings($schema->getDirectives());
        $block     = $this->getDefinitionBlock($settings, $type);
        $list      = $this->getDefinitionList($settings);
        $list[]    = $block;
        $content   = $this->getDefinitionList($settings, true);
        $content[] = $list;

        foreach ($this->getUsedDefinitions($settings, $schema, $block) as $definition) {
            $content[] = $definition;
        }

        return new TypePrinted($content);
    }
}
