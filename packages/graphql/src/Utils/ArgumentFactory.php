<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSetFactory;

use function assert;
use function is_string;
use function reset;

class ArgumentFactory {
    public function __construct(
        protected ArgumentSetFactory $factory,
    ) {
        // empty
    }

    public function getArgument(InputValueDefinitionNode|string $node, mixed $value): Argument {
        $arguments = $this->getArgumentSet($node, $value);
        $argument  = reset($arguments->arguments);

        assert($argument instanceof Argument, 'Arguments cannot be empty');

        return $argument;
    }

    public function getArgumentSet(InputValueDefinitionNode|string $node, mixed $value): ArgumentSet {
        $type      = is_string($node) ? $node : Printer::doPrint($node->type);
        $input     = Parser::inputObjectTypeDefinition("input A { value: {$type} }");
        $arguments = $this->factory->wrapArgs($input, ['value' => $value]);

        return $arguments;
    }
}
