<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Schema;

use GraphQL\Type\Definition\InterfaceType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\TypeDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 *
 * @extends TypeDefinitionBlock<InterfaceType>
 */
#[GraphQLDefinition(InterfaceType::class)]
class InterfaceTypeDefinition extends TypeDefinitionBlock {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        InterfaceType $definition,
    ) {
        parent::__construct($settings, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'interface';
    }
}
