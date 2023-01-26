<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types;

use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

/**
 * @internal
 *
 * @extends TypeDefinitionBlock<ObjectType>
 */
class ObjectTypeDefinitionBlock extends TypeDefinitionBlock {
    public function __construct(
        Settings $settings,
        int $level,
        int $used,
        ObjectType $definition,
    ) {
        parent::__construct($settings, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'type';
    }
}
