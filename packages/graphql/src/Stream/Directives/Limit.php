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
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\FieldArgumentDirective;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;
use Nuwave\Lighthouse\Validation\RulesDirective;

use function config;
use function max;
use function min;
use function strtr;

/**
 * @implements FieldArgumentDirective<int<1, max>>
 */
class Limit extends BaseDirective implements ArgManipulator, FieldArgumentDirective {
    use WithManipulator;

    final public const ArgDefault = 'default';
    final public const ArgMax     = 'max';

    /**
     * @return array{name: string, default: int, max: int}
     */
    final public static function settings(): array {
        $settings = (array) config(Directive::Settings.'.limit');

        return [
            'name'    => Cast::toString($settings['name'] ?? 'limit'),
            'default' => Cast::toInt($settings['default'] ?? 25),
            'max'     => Cast::toInt($settings['max'] ?? 100),
        ];
    }

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

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType,
    ): void {
        // Type
        $type          = Type::nonNull(Type::int());
        $manipulator   = $this->getAstManipulator($documentAST);
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
        return max(
            1,
            Cast::toInt(
                $this->directiveArgValue(self::ArgMax) ?? static::settings()['max'],
            ),
        );
    }

    /**
     * @return int<1, max>
     */
    protected function getArgDefault(): int {
        return min(
            $this->getArgMax(),
            max(
                1,
                Cast::toInt(
                    $this->directiveArgValue(self::ArgDefault) ?? static::settings()['default'],
                ),
            ),
        );
    }

    public function getFieldArgumentValue(ResolveInfo $info, mixed $value): mixed {
        return $value !== null
            ? max(1, Cast::toInt($value))
            : $this->getArgDefault();
    }
}
