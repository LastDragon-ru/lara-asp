<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\FieldArgumentDirective;
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
        $manipulator   = $this->getAstManipulator($documentAST);
        $argDefinition = $manipulator->setArgumentType(
            $parentType,
            $parentField,
            $argDefinition,
            Parser::typeReference('Int'),
        );

        // Default
        // (required to be able to use value from the cursor)
        $argDefinition->defaultValue = null;

        // Description
        $argMin                            = 1;
        $argSize                           = $this->getArgSize();
        $argLimit                          = $this->getArgLimit();
        $argDefinition->description      ??= Parser::stringLiteral(
            <<<'STRING'
            """
            The default value comes from the cursor, or equal to `${size}` if no cursor.
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

    public function getFieldArgumentValue(mixed $value): mixed {
        return $value !== null ? max(1, Cast::toInt($value)) : null;
    }

    public function getFieldArgumentDefault(): mixed {
        return $this->getArgSize();
    }
}
