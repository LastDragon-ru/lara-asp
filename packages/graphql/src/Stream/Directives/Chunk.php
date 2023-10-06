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
class Chunk extends BaseDirective implements ArgManipulator, FieldArgumentDirective {
    use WithManipulator;

    final public const ArgSize  = 'size';
    final public const ArgLimit = 'limit';

    /**
     * @return array{name: string, size: int, limit: int}
     */
    final public static function settings(): array {
        $settings = (array) config(Directive::Settings.'.chunk');

        return [
            'name'  => Cast::toString($settings['name'] ?? 'chunk'),
            'size'  => Cast::toInt($settings['size'] ?? 25),
            'limit' => Cast::toInt($settings['limit'] ?? 100),
        ];
    }

    public static function definition(): string {
        $name     = DirectiveLocator::directiveName(static::class);
        $argSize  = self::ArgSize;
        $argLimit = self::ArgLimit;

        return <<<GRAPHQL
            directive @{$name}(
                {$argSize}: Int
                {$argLimit}: Int
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
        $argSize                     = $this->getArgSize();
        $argDefinition->defaultValue = Cast::to(IntValueNode::class, AST::astFromValue($argSize, $type));

        // Description
        $argMin                            = 1;
        $argLimit                          = $this->getArgLimit();
        $argDefinition->description      ??= Parser::stringLiteral(
            <<<'STRING'
            """
            The value must be between `${min}` and `${limit}`.
            """
            STRING,
        );
        $argDefinition->description->value = strtr($argDefinition->description->value, [
            '${min}'   => $argMin,
            '${size}'  => $argSize,
            '${limit}' => $argLimit,
        ]);

        // Validation
        // todo(graphql/@stream): Not sure that validation works for queries, need to check.
        $manipulator->addDirective(
            $argDefinition,
            RulesDirective::class,
            [
                'apply' => ["min:{$argMin}", "max:{$this->getArgLimit()}"],
            ],
        );
    }

    /**
     * @return int<1, max>
     */
    protected function getArgLimit(): int {
        return max(
            1,
            Cast::toInt(
                $this->directiveArgValue(self::ArgLimit) ?? static::settings()['limit'],
            ),
        );
    }

    /**
     * @return int<1, max>
     */
    protected function getArgSize(): int {
        return min(
            $this->getArgLimit(),
            max(
                1,
                Cast::toInt(
                    $this->directiveArgValue(self::ArgSize) ?? static::settings()['size'],
                ),
            ),
        );
    }

    public function getFieldArgumentValue(ResolveInfo $info, mixed $value): mixed {
        return $value !== null
            ? max(1, Cast::toInt($value))
            : $this->getArgSize();
    }
}
