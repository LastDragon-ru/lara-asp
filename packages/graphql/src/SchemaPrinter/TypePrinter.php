<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedType;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\TypePrinter as TypePrinterContract;

class TypePrinter extends Printer implements TypePrinterContract {
    public function print(Type $type): PrintedType {
        $settings  = $this->getPrinterSettings();
        $content   = $this->getDefinitionList($settings, true);
        $content[] = $this->getDefinitionBlock($settings, $type);
        $printed   = new TypePrinted($content);

        return $printed;
    }
}
