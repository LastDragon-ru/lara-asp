<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Exception;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
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

    public function getArgument(Node|string $node, mixed $value): Argument {
        $arguments = $this->getArgumentSet($node, $value);
        $argument  = reset($arguments->arguments);

        assert($argument instanceof Argument, 'Arguments cannot be empty');

        return $argument;
    }

    public function getArgumentSet(Node|string $node, mixed $value): ArgumentSet {
        // Type
        $type = null;

        if ($node instanceof InputValueDefinitionNode) {
            $type = Printer::doPrint($node->type);
        } elseif (is_string($node)) {
            $type = $node;
        } else {
            // empty
        }

        if (!$type) {
            throw new Exception('fixme'); // fixme(graphql): Throw error if no definition
        }

        $input     = Parser::inputObjectTypeDefinition("input A { value: {$type} }");
        $arguments = $this->factory->wrapArgs($input, ['value' => $value]);

        return $arguments;
    }
}
