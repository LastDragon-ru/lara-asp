<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderInfoProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\BuilderUnknown;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithSource;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\AggregateDirective;
use Nuwave\Lighthouse\Schema\Directives\AllDirective;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Directives\BuilderDirective;
use Nuwave\Lighthouse\Schema\Directives\CountDirective;
use Nuwave\Lighthouse\Schema\Directives\FindDirective;
use Nuwave\Lighthouse\Schema\Directives\FirstDirective;
use Nuwave\Lighthouse\Schema\Directives\RelationDirective;
use Nuwave\Lighthouse\Schema\Directives\WithRelationDirective;
use Nuwave\Lighthouse\Scout\SearchDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use ReflectionFunction;
use ReflectionNamedType;

use function class_exists;
use function is_a;

class BuilderInfoDetector {
    use WithManipulator;
    use WithSource;

    public function __construct(
        readonly protected DirectiveLocator $locator,
    ) {
        // empty
    }

    public function getFieldBuilderInfo(
        DocumentAST $document,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $type,
        FieldDefinitionNode $field,
    ): BuilderInfo {
        $builder = $this->getBuilderInfo($field);

        if (!$builder) {
            $manipulator = $this->getAstManipulator($document);
            $argSource   = $this->getFieldSource($manipulator, $type, $field);

            throw new BuilderUnknown($argSource);
        }

        return $builder;
    }

    public function getFieldArgumentBuilderInfo(
        DocumentAST $document,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $type,
        FieldDefinitionNode $field,
        InputValueDefinitionNode $argument,
    ): BuilderInfo {
        $builder = $this->getBuilderInfo($field);

        if (!$builder) {
            $manipulator = $this->getAstManipulator($document);
            $argSource   = $this->getFieldArgumentSource($manipulator, $type, $field, $argument);

            throw new BuilderUnknown($argSource);
        }

        return $builder;
    }

    protected function getBuilderInfo(Node $node): ?BuilderInfo {
        // Provider?
        $provider = $this->locator->associated($node)->first(static function (Directive $directive): bool {
            return $directive instanceof BuilderInfoProvider;
        });

        if ($provider instanceof BuilderInfoProvider) {
            $builder  = $provider->getBuilderInfo();
            $instance = $builder
                ? $this->getBuilderInfoInstance($builder)
                : null;

            return $instance;
        }

        // Scout?
        $scout = false;

        if ($node instanceof FieldDefinitionNode) {
            foreach ($node->arguments as $argument) {
                if ($this->locator->associatedOfType($argument, SearchDirective::class)->isNotEmpty()) {
                    $scout = true;
                    break;
                }
            }
        }

        if ($scout) {
            return $this->getBuilderInfoInstance(ScoutBuilder::class);
        }

        // Builder?
        $directive = $this->locator->associated($node)->first(static function (Directive $directive): bool {
            return $directive instanceof AllDirective
                || $directive instanceof PaginateDirective
                || $directive instanceof BuilderDirective
                || $directive instanceof RelationDirective
                || $directive instanceof FirstDirective
                || $directive instanceof FindDirective
                || $directive instanceof CountDirective
                || $directive instanceof AggregateDirective
                || $directive instanceof WithRelationDirective;
        });

        if ($directive) {
            $type = null;

            if ($directive instanceof PaginateDirective) {
                $type = $this->getBuilderType($directive, 'builder');
            } elseif ($directive instanceof AllDirective) {
                $type = $this->getBuilderType($directive, 'builder');
            } elseif ($directive instanceof AggregateDirective) {
                $type = $this->getBuilderType($directive, 'builder');
            } elseif ($directive instanceof BuilderDirective) {
                $type = $this->getBuilderType($directive, 'method');
            } else {
                // empty
            }

            return $this->getBuilderInfoInstance($type ?? EloquentBuilder::class);
        }

        // Unknown
        return null;
    }

    /**
     * @return class-string|null
     */
    private function getBuilderType(BaseDirective $directive, string ...$arguments): ?string {
        $type   = null;
        $helper = new class() extends BaseDirective {
            public static function definition(): string {
                return '';
            }

            public function isArgument(BaseDirective $directive, string $argument): bool {
                return $directive->directiveHasArgument($argument);
            }
        };

        foreach ($arguments as $argument) {
            if ($helper->isArgument($directive, $argument)) {
                $resolver = $directive->getResolverFromArgument($argument);
                $return   = (new ReflectionFunction($resolver))->getReturnType();
                $return   = $return instanceof ReflectionNamedType
                    ? $return->getName()
                    : null;

                if ($return && class_exists($return)) {
                    $type = $return;
                }

                break;
            }
        }

        return $type;
    }

    private function getBuilderInfoInstance(BuilderInfo|string $type): ?BuilderInfo {
        return match (true) {
            $type instanceof BuilderInfo              => $type,
            is_a($type, EloquentBuilder::class, true) => new BuilderInfo('', EloquentBuilder::class),
            is_a($type, ScoutBuilder::class, true)    => new BuilderInfo('Scout', ScoutBuilder::class),
            is_a($type, QueryBuilder::class, true)    => new BuilderInfo('Query', QueryBuilder::class),
            is_a($type, Collection::class, true)      => new BuilderInfo('Collection', Collection::class),
            default                                   => null,
        };
    }
}
