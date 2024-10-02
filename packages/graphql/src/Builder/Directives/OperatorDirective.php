<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\DirectiveLocation;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextBuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Override;

use function array_merge;
use function array_unique;
use function implode;

abstract class OperatorDirective extends BaseDirective implements Operator {
    public function __construct(
        protected readonly BuilderFieldResolver $resolver,
    ) {
        // empty
    }

    #[Override]
    public static function definition(): string {
        $name      = '@'.DirectiveLocator::directiveName(static::class);
        $locations = implode(
            ' | ',
            array_unique(
                array_merge(
                    static::locations(),
                    [
                        // Location is mandatory to be able to call the operator
                        DirectiveLocation::INPUT_FIELD_DEFINITION,
                    ],
                ),
            ),
        );

        return <<<GRAPHQL
            directive {$name} on {$locations}
        GRAPHQL;
    }

    /**
     * @return list<string>
     */
    protected static function locations(): array {
        return array_merge(static::getLocations(), [
            // Locations are required to be able to add operators inside the schema
            DirectiveLocation::SCALAR,
            DirectiveLocation::ENUM,
        ]);
    }

    /**
     * @deprecated 6.0.0 Use {@see self::locations()} instead.
     *
     * @return list<string>
     */
    protected static function getLocations(): array {
        return [
            // empty
        ];
    }

    #[Override]
    public function isAvailable(TypeProvider $provider, TypeSource $source, Context $context): bool {
        // Builder?
        $builder = $context->get(HandlerContextBuilderInfo::class)?->value->getBuilder();

        if ($builder === null || !$this->isBuilderSupported($builder)) {
            return false;
        }

        // Ok
        return true;
    }

    /**
     * @param class-string $builder
     */
    abstract protected function isBuilderSupported(string $builder): bool;
}
