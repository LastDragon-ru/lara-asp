<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\InterfaceType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

/**
 * @internal
 *
 * @extends TypeDefinitionBlock<InterfaceType>
 */
class InterfaceTypeDefinitionBlock extends TypeDefinitionBlock {
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
