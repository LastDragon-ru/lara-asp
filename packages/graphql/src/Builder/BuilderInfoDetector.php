<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use Closure;
use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderInfoProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\BuilderUnknown;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithSource;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
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
use Override;
use ReflectionFunction;
use ReflectionNamedType;

use function class_exists;
use function reset;

class BuilderInfoDetector {
    use WithSource;

    public function __construct(
        private readonly ManipulatorFactory $manipulatorFactory,
    ) {
        // empty
    }

    public function getFieldBuilderInfo(
        DocumentAST $document,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $type,
        FieldDefinitionNode $field,
    ): BuilderInfo {
        $manipulator = $this->manipulatorFactory->create($document);
        $fieldSource = $this->getFieldSource($manipulator, $type, $field);
        $builder     = $this->getSourceBuilderInfo($manipulator, $fieldSource);

        return $builder;
    }

    public function getFieldArgumentBuilderInfo(
        DocumentAST $document,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $type,
        FieldDefinitionNode $field,
        InputValueDefinitionNode $argument,
    ): BuilderInfo {
        $manipulator = $this->manipulatorFactory->create($document);
        $argSource   = $this->getFieldArgumentSource($manipulator, $type, $field, $argument);
        $builder     = $this->getSourceBuilderInfo($manipulator, $argSource);

        return $builder;
    }

    protected function getSourceBuilderInfo(
        AstManipulator $manipulator,
        ObjectFieldSource|InterfaceFieldSource|ObjectFieldArgumentSource|InterfaceFieldArgumentSource $source,
    ): BuilderInfo {
        $builder = null;
        $reason  = null;

        try {
            $builder = $this->getBuilderInfo($manipulator, $source);
        } catch (Exception $exception) {
            $reason = $exception;
        }

        if ($builder === null) {
            throw new BuilderUnknown($source, $reason);
        }

        return $builder;
    }

    protected function getBuilderInfo(
        AstManipulator $manipulator,
        ObjectFieldSource|InterfaceFieldSource|ObjectFieldArgumentSource|InterfaceFieldArgumentSource $source,
    ): ?BuilderInfo {
        // Provider?
        $field    = $source instanceof InterfaceFieldArgumentSource || $source instanceof ObjectFieldArgumentSource
            ? $source->getParent()
            : $source;
        $provider = $manipulator->getDirective($field->getField(), BuilderInfoProvider::class);

        if ($provider instanceof BuilderInfoProvider) {
            return $provider->getBuilderInfo($source);
        }

        // Scout?
        $scout = $field->hasArgument(static function (mixed $argument) use ($manipulator): bool {
            return $manipulator->getDirective($argument, SearchDirective::class) !== null;
        });

        if ($scout) {
            return $this->getBuilderInfoInstance(ScoutBuilder::class);
        }

        // Builder?
        $directives = $manipulator->getDirectives(
            $field->getField(),
            null,
            static function (Directive $directive): bool {
                return $directive instanceof AllDirective
                    || $directive instanceof PaginateDirective
                    || $directive instanceof BuilderDirective
                    || $directive instanceof RelationDirective
                    || $directive instanceof FirstDirective
                    || $directive instanceof FindDirective
                    || $directive instanceof CountDirective
                    || $directive instanceof AggregateDirective
                    || $directive instanceof WithRelationDirective;
            },
        );
        $directive  = reset($directives);

        if ($directive instanceof Directive) {
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
            #[Override]
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
                $return   = $this->getCallableReturnType($resolver);

                if ($return !== null) {
                    $type = $return;
                }

                break;
            }
        }

        return $type;
    }

    /**
     * @param class-string $type
     */
    private function getBuilderInfoInstance(string $type): ?BuilderInfo {
        return BuilderInfo::create($type);
    }

    /**
     * @param Closure():mixed $resolver
     *
     * @return class-string|null
     */
    private function getCallableReturnType(Closure $resolver): ?string {
        $return = (new ReflectionFunction($resolver))->getReturnType();
        $return = $return instanceof ReflectionNamedType
            ? $return->getName()
            : null;
        $return = $return !== null && class_exists($return)
            ? $return
            : null;

        return $return;
    }
}
