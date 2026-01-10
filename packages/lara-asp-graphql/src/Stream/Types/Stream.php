<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Types;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive;
use Override;

use function str_ends_with;

class Stream implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public function is(string $type): bool {
        return str_ends_with($type, Directive::Name);
    }

    public function getOriginalTypeName(string $type): string {
        return $this->is($type)
            ? Str::singular(Str::beforeLast($type, Directive::Name))
            : $type;
    }

    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        return Str::plural(Str::studly($source->getTypeName())).Directive::Name;
    }

    #[Override]
    public function getTypeDefinition(
        Manipulator $manipulator,
        TypeSource $source,
        Context $context,
        string $name,
    ): (TypeDefinitionNode&Node)|string|null {
        $type       = $source->getTypeName();
        $navigation = $manipulator->getType(Navigation::class, $source, $context);

        return Parser::objectTypeDefinition(
            <<<GRAPHQL
            type {$name} {
                """
                Requested items.
                """
                items: [{$type}!]!

                """
                Total number of items. Not recommended querying it in each query
                due to performance.
                """
                length: Int

                """
                Offsets/Cursors to navigate within the stream.
                """
                navigation: {$navigation}!
            }
            GRAPHQL,
        );
    }
}
