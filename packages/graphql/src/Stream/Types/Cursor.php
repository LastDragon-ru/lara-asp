<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive;

class Cursor extends StringType implements TypeDefinition {
    public string  $name        = Directive::Name.'Cursor';
    public ?string $description = <<<'DESCRIPTION'
        Represents a cursor for the `@stream` directive. The value can be a
        positive `Int` or a `String`. The `Int` value represents the offset
        (zero-based) to navigate to any position within the stream (= cursor
        pagination). And the `String` value represents the cursor and allows
        navigation only to the previous/next pages (= offset pagination).
        DESCRIPTION;

    public function getTypeName(Manipulator $manipulator, BuilderInfo $builder, TypeSource $source): string {
        return $this->name();
    }

    public function getTypeDefinition(
        Manipulator $manipulator,
        string $name,
        TypeSource $source,
    ): TypeDefinitionNode|Type|null {
        return $this;
    }
}
