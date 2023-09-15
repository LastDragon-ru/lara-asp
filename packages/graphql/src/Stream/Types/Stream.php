<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive;
use function str_ends_with;

class Stream implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function is(string $type): bool {
        return str_ends_with($type, Directive::Name);
    }

    public static function getOriginalTypeName(string $type): string {
        return static::is($type)
            ? Str::singular(Str::beforeLast($type, Directive::Name))
            : $type;
    }

    public function getTypeName(Manipulator $manipulator, BuilderInfo $builder, TypeSource $source): string {
        return Str::plural(Str::studly($source->getTypeName())).Directive::Name;
    }

    public function getTypeDefinition(
        Manipulator $manipulator,
        string $name,
        TypeSource $source,
    ): TypeDefinitionNode|Type|null {
        $type       = $source->getTypeName();
        $info       = $manipulator->getType(Info::class, $source);
        $navigator  = $manipulator->getType(Navigator::class, $source);
        $aggregator = $manipulator->getType(Aggregator::class, $source);

        return Parser::objectTypeDefinition(
            <<<GRAPHQL
            type {$name} {
                items: [{$type}!]!
                info: {$info}!
                navigator: {$navigator}!
                aggregator: {$aggregator}!
            }
            GRAPHQL,
        );
    }
}
