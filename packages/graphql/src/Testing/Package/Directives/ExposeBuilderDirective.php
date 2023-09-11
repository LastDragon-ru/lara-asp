<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Directives;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderInfoProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

use function hash;

/**
 * @internal
 */
class ExposeBuilderDirective extends BaseDirective implements FieldResolver, BuilderInfoProvider {
    /**
     * @var QueryBuilder|EloquentBuilder<Model>|ScoutBuilder
     */
    public static QueryBuilder|EloquentBuilder|ScoutBuilder $builder;

    /**
     * @var QueryBuilder|EloquentBuilder<Model>|ScoutBuilder|Relation<Model>|null
     */
    public static QueryBuilder|EloquentBuilder|ScoutBuilder|Relation|null $result = null;

    public static function definition(): string {
        $name = static::getName();

        return <<<GRAPHQL
            directive {$name} on FIELD_DEFINITION
        GRAPHQL;
    }

    public static function getName(): string {
        return '@exposeBuilder'.hash('sha256', static::class);
    }

    public function getBuilderInfo(TypeSource $source): BuilderInfo|string|null {
        return static::$builder::class;
    }

    public function resolveField(FieldValue $fieldValue): callable {
        return static function (mixed $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array {
            static::$result = $resolveInfo->enhanceBuilder(
                static::$builder,
                [],
                $root,
                $args,
                $context,
                $resolveInfo,
            );

            return [];
        };
    }
}
