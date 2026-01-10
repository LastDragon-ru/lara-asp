# DIY Parser

There are several tools to generate full-featured parsers even for PHP[^1]. They are overkill when you just need to parse something simple. In such cases, you might decide to create your own parser. There are a lot of articles/examples on the web, and actually it is not too difficult as you may think. This is yet another package to simplify writing fast and memory-effective parsers that can parse infinite strings.

[^1]: <https://en.wikipedia.org/wiki/Comparison_of_parser_generators>

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: preprocess/78cfc4c7c7c55577)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.4` |  `HEAD`  ,  `9.2.0`   |
|  | `^8.3` |  `HEAD`  ,  `9.2.0`   |

[//]: # (end: preprocess/78cfc4c7c7c55577)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "text-parser"}})
[//]: # (start: preprocess/61edde0e805c71f6)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/text-parser
```

[//]: # (end: preprocess/61edde0e805c71f6)

# Introduction

As an example, I will show how to create a parser for mathematical expressions like `2 - (1 + 2) / 3` and how to calculate them as a bonus.

[include:example]: ./docs/Examples/Calculator.php
[//]: # (start: preprocess/a4933bd628ef331b)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Docs\Calculator\Calculator;

Example::dump(
    (new Calculator())->calculate('2 - (1 + 2) / 3'),
);
```

The `(new Calculator())->calculate('2 - (1 + 2) / 3')` is:

```plain
1
```

[//]: # (end: preprocess/a4933bd628ef331b)

Our mathematical expression will be a string, subject to the following rules:

* numbers (positive integers only)
* operators (addition `+`, subtraction `-`, multiplication `*`, and division `*`)
* sub-expressions inside `(` and `)`
* space(s) ` ` that can be used as delimiters to increase readability
* Numbers and sub-expressions should be separated by an operator

We will start from the end, from the complete implementation of the parser, and then I will describe what is going inside it. So

[include:example]: ./docs/Calculator/Parser.php
[//]: # (start: preprocess/c5d50a0ee08674d8)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator;

use LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNodeChild;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNodeFactory;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorAdditionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorDivisionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorMultiplicationNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorNode;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorSubtractionNode;
use LastDragon_ru\TextParser\Iterables\TransactionalIterable;
use LastDragon_ru\TextParser\Tokenizer\Token;
use LastDragon_ru\TextParser\Tokenizer\Tokenizer;
use LogicException;

use function filter_var;
use function preg_match;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;

class Parser {
    public function __construct() {
        // empty
    }

    public function parse(string $pattern): ?ExpressionNode {
        $node = null;

        try {
            $iterable = (new Tokenizer(Name::class))->tokenize([$pattern]);
            $node     = $this->parseExpression($iterable);
        } catch (LogicException) {
            // The `$pattern` is not a valid expression
        }

        return $node;
    }

    /**
     * @param iterable<mixed, Token<Name>> $iterable
     */
    protected function parseExpression(iterable $iterable): ?ExpressionNode {
        $iterable = new TransactionalIterable($iterable, 64, 1);
        $factory  = new ExpressionNodeFactory();

        while ($iterable->valid()) {
            $factory->push($this->parseExpressionChild($iterable));
        }

        return $factory->create();
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseExpressionChild(TransactionalIterable $iterable): ?ExpressionNodeChild {
        return $this->parseSubExpression($iterable)
            ?? $this->parseOperator($iterable)
            ?? $this->parseNumber($iterable)
            ?? $this->parseSpace($iterable);
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseSubExpression(TransactionalIterable $iterable): ?ExpressionNode {
        // Is `(`?
        if ($iterable[0]?->is(Name::LeftParenthesis) !== true) {
            return null;
        }

        // Begin
        $iterable->begin();
        $iterable->next();

        // Parse
        $node    = null;
        $factory = new ExpressionNodeFactory();

        while ($iterable->valid()) {
            // Is `)`?
            if ($iterable[0]?->is(Name::RightParenthesis) === true) {
                $node = $factory->create();

                $iterable->next();

                break;
            }

            // Child
            $factory->push(
                $this->parseExpressionChild($iterable),
            );
        }

        // Commit
        $iterable->end($node);

        // Return
        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseOperator(TransactionalIterable $iterable): ?OperatorNode {
        $node = match ($iterable[0]->name ?? null) {
            Name::Plus     => new OperatorAdditionNode(),
            Name::Minus    => new OperatorSubtractionNode(),
            Name::Asterisk => new OperatorMultiplicationNode(),
            Name::Slash    => new OperatorDivisionNode(),
            default        => null,
        };

        if ($node !== null) {
            $iterable->next();
        }

        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseNumber(TransactionalIterable $iterable): ?NumberNode {
        // String?
        if ($iterable[0]?->is(null) !== true) {
            return null;
        }

        // Number?
        $number = $iterable[0]->value;
        $number = preg_match('/^[0-9]+$/u', $number) === 1
            ? filter_var($number, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)
            : null;
        $node   = $number !== null ? new NumberNode($number) : null;

        if ($node !== null) {
            $iterable->next();
        }

        // Return
        return $node;
    }

    /**
     * @param TransactionalIterable<Token<Name>> $iterable
     */
    protected function parseSpace(TransactionalIterable $iterable): null {
        // Only spaces allowed here
        if ($iterable[0]?->is(Name::Space) !== true) {
            throw new LogicException('The string is not a mathematical expression.');
        } else {
            $iterable->next();
        }

        return null;
    }
}
```

[//]: # (end: preprocess/c5d50a0ee08674d8)

And the resulting AST:

[include:example]: ./docs/Examples/Parser.php
[//]: # (start: preprocess/ec94ccf0933d552f)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Docs\Calculator\Parser;

Example::dump(
    (new Parser())->parse('2 - (1 + 2) / 3'),
);
```

The `(new Parser())->parse('2 - (1 + 2) / 3')` is:

```plain
LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode {
  +children: [
    LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode {
      +value: 2
    },
    LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorSubtractionNode {},
    LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode {
      +children: [
        LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode {
          +value: 1
        },
        LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorAdditionNode {},
        LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode {
          +value: 2
        },
      ]
    },
    LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorDivisionNode {},
    LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode {
      +value: 3
    },
  ]
}
```

[//]: # (end: preprocess/ec94ccf0933d552f)

# Tokenizer

First, we should split the input string into tokens that is happening inside the [`Parser::parse()`][code-links/b84ae5a44e02f512]. The [`Tokenizer`][code-links/680ddd300ebebd55] accept a backed enum(s) and uses their values as delimiters (value can be a single character or a string). According to our rules, we need the following tokens:

[include:example]: ./docs/Calculator/Name.php
[//]: # (start: preprocess/4a7c6a4d1e17a5e4)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator;

enum Name: string {
    case Plus             = '+';
    case Minus            = '-';
    case Asterisk         = '*';
    case Slash            = '/';
    case Space            = ' ';
    case LeftParenthesis  = '(';
    case RightParenthesis = ')';
}
```

[//]: # (end: preprocess/4a7c6a4d1e17a5e4)

After tokenization, we will have the list of tokens for further processing. Note that [`Token::$name`][code-links/0d92bddcd098f4b5] contains the enum case or `null` for strings.

[include:example]: ./docs/Examples/Tokenizer.php
[//]: # (start: preprocess/726e983b5650acd2)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Docs\Calculator\Name;
use LastDragon_ru\TextParser\Tokenizer\Tokenizer;

use function iterator_to_array;

$input     = '2 - (1 + 2) / 3';
$tokenizer = new Tokenizer(Name::class);
$tokens    = $tokenizer->tokenize([$input]);

Example::dump(iterator_to_array($tokens));
```

<details><summary>Example output</summary>

The `iterator_to_array($tokens)` is:

```plain
[
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: null
    +value: "2"
    +offset: 0
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {#1
      +name: "Space"
      +value: " "
    }
    +value: " "
    +offset: 1
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {
      +name: "Minus"
      +value: "-"
    }
    +value: "-"
    +offset: 2
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {#1}
    +value: " "
    +offset: 3
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {
      +name: "LeftParenthesis"
      +value: "("
    }
    +value: "("
    +offset: 4
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: null
    +value: "1"
    +offset: 5
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {#1}
    +value: " "
    +offset: 6
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {
      +name: "Plus"
      +value: "+"
    }
    +value: "+"
    +offset: 7
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {#1}
    +value: " "
    +offset: 8
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: null
    +value: "2"
    +offset: 9
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {
      +name: "RightParenthesis"
      +value: ")"
    }
    +value: ")"
    +offset: 10
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {#1}
    +value: " "
    +offset: 11
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {
      +name: "Slash"
      +value: "/"
    }
    +value: "/"
    +offset: 12
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Calculator\Name {#1}
    +value: " "
    +offset: 13
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: null
    +value: "3"
    +offset: 14
  },
]
```

</details>

[//]: # (end: preprocess/726e983b5650acd2)

# Parser

Next, we pass tokens into [`Parser::parseExpression()`][code-links/9110a629f7b2dbe5] where we create the instance of [`TransactionalIterable`][code-links/080b62df2cf20def] and start converting tokens into AST. The [`TransactionalIterable`][code-links/080b62df2cf20def] is very important - it's not only return current/next/previous token and to navigate back and forward, but support transactions to process nested structures (e.g. sub-expressions) and rollback if parsing failed (eg if `)` is missed).

[include:example]: ./docs/Examples/TransactionalIterable.php
[//]: # (start: preprocess/b06c2cb28b3533e3)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Docs\Calculator\Name;
use LastDragon_ru\TextParser\Iterables\TransactionalIterable;
use LastDragon_ru\TextParser\Tokenizer\Tokenizer;

$input    = '1 + 2';
$tokens   = (new Tokenizer(Name::class))->tokenize([$input]);
$iterable = new TransactionalIterable($tokens, 5, 5);

Example::dump($iterable[0]);
Example::dump($iterable[4]);

$iterable->next(2);
$iterable->begin();     // start nested

Example::dump($iterable[-2]);
Example::dump($iterable[0]);
Example::dump($iterable[2]);

$iterable->rollback();  // oops

Example::dump($iterable[0]);
```

<details><summary>Example output</summary>

The `$iterable[0]` is:

```plain
LastDragon_ru\TextParser\Tokenizer\Token {
  +name: null
  +value: "1"
  +offset: 0
}
```

The `$iterable[4]` is:

```plain
LastDragon_ru\TextParser\Tokenizer\Token {
  +name: null
  +value: "2"
  +offset: 4
}
```

The `$iterable[-2]` is:

```plain
LastDragon_ru\TextParser\Tokenizer\Token {
  +name: null
  +value: "1"
  +offset: 0
}
```

The `$iterable[0]` is:

```plain
LastDragon_ru\TextParser\Tokenizer\Token {
  +name: LastDragon_ru\TextParser\Docs\Calculator\Name {
    +name: "Plus"
    +value: "+"
  }
  +value: "+"
  +offset: 2
}
```

The `$iterable[2]` is:

```plain
LastDragon_ru\TextParser\Tokenizer\Token {
  +name: null
  +value: "2"
  +offset: 4
}
```

The `$iterable[0]` is:

```plain
LastDragon_ru\TextParser\Tokenizer\Token {
  +name: LastDragon_ru\TextParser\Docs\Calculator\Name {
    +name: "Plus"
    +value: "+"
  }
  +value: "+"
  +offset: 2
}
```

</details>

[//]: # (end: preprocess/b06c2cb28b3533e3)

All other methods just create AST nodes and check that the expression is correct. You may notice the [`ExpressionNodeFactory`][code-links/4564b80f054a86d3] class, which is a subclass of [`NodeParentFactory`][code-links/c6d0471cd80ce86b]. In our case, the [`ExpressionNodeFactory`][code-links/4564b80f054a86d3] helps to simplify the code, and it also checks that an operator between numbers/expressions (one of our requirements).

In the general case, the main reason of [`NodeParentFactory`][code-links/c6d0471cd80ce86b] is merging sequence of child nodes (of the same class) into one node. The [`Tokenizer`][code-links/680ddd300ebebd55] may generate a lot of "string" tokens (especially if escaping is supported), but we usually want only one (`abc`) node in AST instead of multiple (`a`, `b`, and `c`) nodes. If you use static analysis tools like PHPStan, the class will guaranties the type safety too.

[include:example]: ./docs/Examples/NodeParentFactory.php
[//]: # (start: preprocess/74b6424176e7a142)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Ast\NodeChild;
use LastDragon_ru\TextParser\Ast\NodeParentFactory;
use LastDragon_ru\TextParser\Ast\NodeParentImpl;
use LastDragon_ru\TextParser\Ast\NodeString;
use Override;

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

/**
 * @implements NodeChild<ParentNode>
 */
class ChildNode extends NodeString implements NodeChild {
    // empty
}

/**
 * @extends NodeParentImpl<ChildNode>
 */
class ParentNode extends NodeParentImpl {
    // empty
}

/**
 * @extends NodeParentFactory<ParentNode, ChildNode>
 */
class ParentNodeFactory extends NodeParentFactory {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        return $children !== [] ? new ParentNode($children) : null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return true;
    }
}

$factory = new ParentNodeFactory();

$factory->push(new ChildNode('a'));
$factory->push(new ChildNode('b'));
$factory->push(new ChildNode('c'));

Example::dump($factory->create()); // create and reset
Example::dump($factory->create()); // `null`
```

The `$factory->create()` is:

```plain
LastDragon_ru\TextParser\Docs\Examples\ParentNode {
  +children: [
    LastDragon_ru\TextParser\Docs\Examples\ChildNode {
      +string: "abc"
    },
  ]
}
```

The `$factory->create()` is:

```plain
null
```

[//]: # (end: preprocess/74b6424176e7a142)

# AST

As you can see, our AST for mathematical expressions doesn't have references to parent/next/previous nodes. It makes the parser simple, and reduce memory usage. The [`Cursor`][code-links/c6b0ac8163d25254] class can be used to navigate the AST in all directions. Please note that your nodes must implement [`NodeParent`][code-links/03bf4306de68ce04] and [`NodeChild`][code-links/75d9e2fb8399ea2e] if you want to use the cursor.

[include:example]: ./docs/Examples/Cursor.php
[//]: # (start: preprocess/613e86b97662c891)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Ast\Cursor;
use LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode;
use LastDragon_ru\TextParser\Docs\Calculator\Parser;

use function assert;

// Parse
$ast = (new Parser())->parse('2 - (1 + 2) / 3');

assert($ast instanceof ExpressionNode);

// Create the cursor
$cursor = new Cursor($ast);

// Children can be iterated directly
foreach ($cursor as $child) {
    if ($child->node instanceof ExpressionNode) {
        Example::dump($child->node);
        break;
    }
}

// Also possible to get n-th child
Example::dump($cursor[2]);

// And next/previous
Example::dump($cursor[2]->next->node ?? null);
Example::dump($cursor[2]->previous->node ?? null);
```

<details><summary>Example output</summary>

```plain
LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode {
  +children: [
    LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode {
      +value: 1
    },
    LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorAdditionNode {},
    LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode {
      +value: 2
    },
  ]
}
```

The `$cursor[2]` is:

```plain
LastDragon_ru\TextParser\Ast\Cursor {
  +node: LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode {#1
    +children: [
      LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode {
        +value: 1
      },
      LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorAdditionNode {},
      LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode {
        +value: 2
      },
    ]
  }
  +parent: LastDragon_ru\TextParser\Ast\Cursor {
    +node: LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode {
      +children: [
        LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode {
          +value: 2
        },
        LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorSubtractionNode {},
        LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode {#1},
        LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorDivisionNode {},
        LastDragon_ru\TextParser\Docs\Calculator\Ast\NumberNode {
          +value: 3
        },
      ]
    }
    +parent: null
    +index: null
  }
  +index: 2
}
```

The `$cursor[2]->next->node ?? null` is:

```plain
LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorDivisionNode {}
```

The `$cursor[2]->previous->node ?? null` is:

```plain
LastDragon_ru\TextParser\Docs\Calculator\Ast\OperatorSubtractionNode {}
```

</details>

[//]: # (end: preprocess/613e86b97662c891)

# Escape

Of course, it is supported. You just need to specify the token to use as escaping string:

[include:example]: ./docs/Examples/Escape.php
[//]: # (start: preprocess/49c559faa304d8f3)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Tokenizer\Tokenizer;

use function iterator_to_array;

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

enum Name: string {
    case Slash     = '/';
    case Backslash = '\\';
}

$input     = 'a/b\\/\\c';
$tokenizer = new Tokenizer(Name::class, Name::Backslash);
$tokens    = $tokenizer->tokenize([$input]);

Example::dump(iterator_to_array($tokens));
```

The `iterator_to_array($tokens)` is:

```plain
[
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: null
    +value: "a"
    +offset: 0
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Examples\Name {
      +name: "Slash"
      +value: "/"
    }
    +value: "/"
    +offset: 1
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: null
    +value: "b"
    +offset: 2
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: null
    +value: "/"
    +offset: 4
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: LastDragon_ru\TextParser\Docs\Examples\Name {
      +name: "Backslash"
      +value: "\"
    }
    +value: "\"
    +offset: 5
  },
  LastDragon_ru\TextParser\Tokenizer\Token {
    +name: null
    +value: "c"
    +offset: 6
  },
]
```

[//]: # (end: preprocess/49c559faa304d8f3)

# Limitations

Internally, the package uses `preg_match()` with `u` modifier to split string(s) into tokens because this is probably the faster way in PHP (if you have a better way, please create issue/pr/etc). The con is internal encoding is always `UTF-8`. Thus, if you need to parse strings in other encoding, you must convert them into `UTF-8` before parsing.

Multiple tokens that are the same string/character are not supported yet. Moreover, if conflicted, no error will be reported. The priority is undefined.

The input string potentially may have infinite length. But, [`Token::$offset`][code-links/f66047d5bcf6fb77] has `int` type so its max value is `PHP_INT_MAX`. Also, it may be limited by the size of the [`TransactionalIterable`][code-links/080b62df2cf20def] buffers (actual for nested nodes).

There are no line numbers, only [`Token::$offset`][code-links/f66047d5bcf6fb77] from the beginning of the string. If you need them, you need to add token(s) for EOL and process/calculate within the parser.

There is also no special code to check nested/closed parentheses or throw an error if string cannot be parsed. It also should be done within parser implementation.

# Bonus

For calculate mathematical expressions, we are using [shunting yard algorithm](https://en.wikipedia.org/wiki/Shunting_yard_algorithm) to convert our AST into [Reverse Polish Notation](https://en.wikipedia.org/wiki/Reverse_Polish_notation). Implementation is ever simpler than usual, because `(...)` represented as sub-nodes and no need any special actions, please see the [`ExpressionNode`][code-links/c6b92ea7e293ac9d] class.

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: preprocess/c4ba75080f5a48b7)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: preprocess/c4ba75080f5a48b7)

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/c6b0ac8163d25254]: src/Ast/Cursor.php
    "\LastDragon_ru\TextParser\Ast\Cursor"

[code-links/75d9e2fb8399ea2e]: src/Ast/NodeChild.php
    "\LastDragon_ru\TextParser\Ast\NodeChild"

[code-links/03bf4306de68ce04]: src/Ast/NodeParent.php
    "\LastDragon_ru\TextParser\Ast\NodeParent"

[code-links/c6d0471cd80ce86b]: src/Ast/NodeParentFactory.php
    "\LastDragon_ru\TextParser\Ast\NodeParentFactory"

[code-links/c6b92ea7e293ac9d]: docs/Calculator/Ast/ExpressionNode.php
    "\LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNode"

[code-links/4564b80f054a86d3]: docs/Calculator/Ast/ExpressionNodeFactory.php
    "\LastDragon_ru\TextParser\Docs\Calculator\Ast\ExpressionNodeFactory"

[code-links/b84ae5a44e02f512]: docs/Calculator/Parser.php#L30-L41
    "\LastDragon_ru\TextParser\Docs\Calculator\Parser::parse()"

[code-links/9110a629f7b2dbe5]: docs/Calculator/Parser.php#L43-L55
    "\LastDragon_ru\TextParser\Docs\Calculator\Parser::parseExpression()"

[code-links/080b62df2cf20def]: src/Iterables/TransactionalIterable.php
    "\LastDragon_ru\TextParser\Iterables\TransactionalIterable"

[code-links/0d92bddcd098f4b5]: src/Tokenizer/Token.php#L14-L17
    "\LastDragon_ru\TextParser\Tokenizer\Token::$name"

[code-links/f66047d5bcf6fb77]: src/Tokenizer/Token.php#L19
    "\LastDragon_ru\TextParser\Tokenizer\Token::$offset"

[code-links/680ddd300ebebd55]: src/Tokenizer/Tokenizer.php
    "\LastDragon_ru\TextParser\Tokenizer\Tokenizer"

[//]: # (end: code-links)
