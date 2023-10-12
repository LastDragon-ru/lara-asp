<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfoDetector;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithSource;
use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\FieldArgumentDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Cursor as StreamCursor;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\Client\CursorInvalidPath;
use LastDragon_ru\LaraASP\GraphQL\Stream\Types\Cursor as CursorType;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function array_map;
use function config;
use function implode;
use function is_int;
use function max;

/**
 * @implements FieldArgumentDirective<StreamCursor>
 */
class Cursor extends BaseDirective implements ArgManipulator, FieldArgumentDirective {
    use WithManipulator;
    use WithSource;

    /**
     * @return array{name: string}
     */
    final public static function settings(): array {
        $settings = (array) config(Directive::Settings.'.cursor');

        return [
            'name' => Cast::toString($settings['name'] ?? 'cursor'),
        ];
    }

    public static function definition(): string {
        $name = DirectiveLocator::directiveName(static::class);

        return <<<GRAPHQL
            directive @{$name} on ARGUMENT_DEFINITION
        GRAPHQL;
    }

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType,
    ): void {
        $detector    = Container::getInstance()->make(BuilderInfoDetector::class);
        $builder     = $detector->getFieldBuilderInfo($documentAST, $parentType, $parentField);
        $manipulator = $this->getManipulator($documentAST, $builder);
        $source      = $this->getFieldArgumentSource($manipulator, $parentType, $parentField, $argDefinition);
        $type        = Parser::typeReference($manipulator->getType(CursorType::class, $source));

        $manipulator->setArgumentType(
            $parentType,
            $parentField,
            $argDefinition,
            $type,
        );

        $argDefinition->description ??= Parser::stringLiteral(
            <<<'STRING'
            """
            The cursor or offset within the stream to start.
            """
            STRING,
        );
    }

    public function getFieldArgumentValue(ResolveInfo $info, mixed $value): mixed {
        $path = $this->getPath($info);

        if ($value instanceof StreamCursor) {
            if ($path !== $value->path) {
                throw new CursorInvalidPath($path, $value->path);
            }

            // fixme(graphql)!: if args given, probable we need to compare hash
            //      of them with the hash from `$cursor` and throw an error if
            //      doesn't match.
        } elseif (is_int($value)) {
            $value = new StreamCursor(
                path  : $path,
                cursor: null,
                offset: max(0, Cast::toInt($value)),
            );
        } else {
            $value = new StreamCursor(
                path  : $path,
                cursor: null,
                offset: 0,
            );
        }

        return $value;
    }

    protected function getPath(ResolveInfo $info): string {
        $path = array_map(static fn ($path) => is_int($path) ? '*' : $path, $info->path);
        $path = implode('.', $path);

        return $path;
    }
}
