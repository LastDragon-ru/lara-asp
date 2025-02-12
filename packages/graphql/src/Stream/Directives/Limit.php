<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Type;
use GraphQL\Utils\AST;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\ManipulatorFactory;
use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\FieldArgumentDirective;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;
use Nuwave\Lighthouse\Validation\RulesDirective;
use Override;

use function max;
use function min;
use function strtr;

/**
 * @implements FieldArgumentDirective<int<1, max>>
 */
class Limit extends BaseDirective implements ArgManipulator, FieldArgumentDirective {
    final public const string ArgDefault = 'default';
    final public const string ArgMax     = 'max';

    public function __construct(
        protected readonly PackageConfig $config,
        private readonly ManipulatorFactory $manipulatorFactory,
    ) {
        // empty
    }

    #[Override]
    public static function definition(): string {
        $name       = DirectiveLocator::directiveName(static::class);
        $argMax     = self::ArgMax;
        $argDefault = self::ArgDefault;

        return <<<GRAPHQL
            directive @{$name}(
                {$argDefault}: Int
                {$argMax}: Int
            ) on ARGUMENT_DEFINITION
        GRAPHQL;
    }

    #[Override]
    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType,
    ): void {
        // Type
        $type          = Type::nonNull(Type::int());
        $manipulator   = $this->manipulatorFactory->create($documentAST);
        $argDefinition = $manipulator->setArgumentType(
            $parentType,
            $parentField,
            $argDefinition,
            $type,
        );

        // Default
        $argDefault                  = $this->getArgDefault();
        $argDefinition->defaultValue = Cast::to(IntValueNode::class, AST::astFromValue($argDefault, $type));

        // Description
        $argMin                            = 1;
        $argMax                            = $this->getArgMax();
        $argDefinition->description      ??= Parser::stringLiteral(
            <<<'STRING'
            """
            Maximum count of items to return. The value must be between `${min}` and `${max}`.
            """
            STRING,
        );
        $argDefinition->description->value = strtr($argDefinition->description->value, [
            '${min}'     => $argMin,
            '${max}'     => $argMax,
            '${default}' => $argDefault,
        ]);

        // Validation
        $manipulator->addDirective(
            $argDefinition,
            RulesDirective::class,
            [
                'apply' => ['integer', "min:{$argMin}", "max:{$argMax}"],
            ],
        );
    }

    /**
     * @return int<1, max>
     */
    protected function getArgMax(): int {
        $value = $this->directiveArgValue(self::ArgMax) ?? $this->config->getInstance()->stream->limit->max;
        $value = Cast::toInt($value);
        $value = max(1, $value);

        return $value;
    }

    /**
     * @return int<1, max>
     */
    protected function getArgDefault(): int {
        $max   = $this->getArgMax();
        $value = $this->directiveArgValue(self::ArgDefault) ?? $this->config->getInstance()->stream->limit->default;
        $value = Cast::toInt($value);
        $value = min($max, max(1, $value));

        return $value;
    }

    #[Override]
    public function getFieldArgumentValue(ResolveInfo $info, mixed $value): mixed {
        $value = Cast::toIntNullable($value);
        $value = $value !== null ? max(1, $value) : $this->getArgDefault();

        return $value;
    }
}
