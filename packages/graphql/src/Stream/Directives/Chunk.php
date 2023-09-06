<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;
use Nuwave\Lighthouse\Validation\RulesDirective;

use function config;
use function json_encode;
use function min;

use const JSON_THROW_ON_ERROR;

class Chunk extends BaseDirective implements ArgManipulator {
    use WithManipulator;

    private const      Settings = Directive::Settings.'.chunk';
    final public const ArgSize  = 'size';
    final public const ArgLimit = 'limit';

    /**
     * @return array{name: string, size: int, limit: int}
     */
    public static function settings(): array {
        $settings = (array) config(self::Settings);

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
        // Update type
        $settings      = static::settings();
        $manipulator   = $this->getAstManipulator($documentAST);
        $argDefinition = $manipulator->setArgumentType(
            $parentType,
            $parentField,
            $argDefinition,
            Parser::typeReference('Int!'),
        );

        // Default
        $argLimitValue               = Cast::toInt(
            $this->directiveArgValue(self::ArgLimit) ?? $settings['limit'],
        );
        $argSizeValue                = min(
            $argLimitValue,
            Cast::toInt(
                $this->directiveArgValue(self::ArgSize) ?? $settings['size'],
            ),
        );
        $argDefinition->defaultValue = Parser::valueLiteral(
            json_encode($argSizeValue, JSON_THROW_ON_ERROR),
        );

        // Validation
        // todo(graphql/@stream): Not sure that validation works for queries, need to check.
        $manipulator->addDirective(
            $argDefinition,
            RulesDirective::class,
            [
                'apply' => ['min:0', "max:{$argLimitValue}"],
            ],
        );
    }
}
