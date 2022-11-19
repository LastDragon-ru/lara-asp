<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Scalar;

class RelationType implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getTypeName(BuilderInfo $builder, ?string $type, ?bool $nullable): string {
        $directiveName = Directive::Name;
        $builderName   = $builder->getName();
        $operatorName  = Str::studly(Relation::getName());

        return "{$directiveName}{$builderName}Complex{$operatorName}{$type}";
    }

    public function getTypeDefinitionNode(
        Manipulator $manipulator,
        string $name,
        ?string $type,
        ?bool $nullable,
    ): ?TypeDefinitionNode {
        if (!$type) {
            return null;
        }

        $count    = $manipulator->getType(Scalar::class, Type::INT, false);
        $where    = $manipulator->getType(Condition::class, $type, $nullable);
        $typeName = $manipulator->getNodeTypeFullName($type);

        return Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Conditions for the related objects (`has()`/`doesntHave()`) for `{$typeName}`.

            See also:
            * https://laravel.com/docs/eloquent-relationships#querying-relationship-existence
            * https://laravel.com/docs/eloquent-relationships#querying-relationship-absence
            """
            input {$name} {
                """
                Additional conditions.
                """
                where: {$where}

                """
                Count conditions.
                """
                count: {$count}

                """
                Alias for `count: {greaterThanOrEqual: 1}`. Will be ignored if `count` used.
                """
                exists: Boolean

                """
                Alias for `count: {lessThan: 1}`. Will be ignored if `count` used.
                """
                notExists: Boolean! = false
            }
            DEF,
        );
    }
}
