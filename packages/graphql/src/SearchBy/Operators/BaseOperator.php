<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use GraphQL\Language\AST\DirectiveNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;

use function implode;

abstract class BaseOperator extends BaseDirective implements Operator {
    public function __construct() {
        // empty
    }

    public static function getDirectiveName(): string {
        return implode('', [
            '@',
            Str::camel(Directive::Name),
            'Operator',
            Str::studly(static::getName()),
        ]);
    }

    public static function definition(): string {
        $name = static::getDirectiveName();

        return /** @lang GraphQL */ <<<GRAPHQL
            directive ${name} on INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    public function getFieldType(TypeProvider $provider, string $type): ?string {
        return null;
    }

    public function getFieldDirective(): ?DirectiveNode {
        return $this->directiveNode;
    }

    public function isBuilderSupported(object $builder): bool {
        return $builder instanceof EloquentBuilder
            || $builder instanceof QueryBuilder;
    }
}
