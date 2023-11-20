<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Scalar;
use Override;

class RelationType implements TypeDefinition {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getTypeName(Manipulator $manipulator, BuilderInfo $builder, TypeSource $source): string {
        $typeName      = $source->getTypeName();
        $builderName   = $builder->getName();
        $operatorName  = Str::studly(Relation::getName());
        $directiveName = Directive::Name;

        return "{$directiveName}{$builderName}Complex{$operatorName}{$typeName}";
    }

    #[Override]
    public function getTypeDefinition(
        Manipulator $manipulator,
        string $name,
        TypeSource $source,
    ): TypeDefinitionNode|Type|null {
        $count = $manipulator->getType(Scalar::class, $manipulator->getTypeSource(Type::nonNull(Type::int())));
        $where = $manipulator->getType(Condition::class, $source);

        return Parser::inputObjectTypeDefinition(
            <<<GRAPHQL
            """
            Conditions for the related objects (`has()`/`doesntHave()`) for `{$source}`.

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
            GRAPHQL,
        );
    }
}
