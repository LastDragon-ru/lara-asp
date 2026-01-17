# Glob

Full-featured well-tested glob pattern parser and matcher: basic matching (`?`, `*`), globstar (`**`), extglob (`?(pattern-list)`, `*(pattern-list)`, `+(pattern-list)`, `@(pattern-list)`, `!(pattern-list)`), brace expansion (`{a,b,c}.txt`, `{1..3}.txt`, etc), dotglob, nocasematch, POSIX Named character classes (`[:alnum:]`, etc), POSIX Collating symbols (`[.ch.]`, etc), POSIX Equivalence class expressions (`[=a=]`, etc)[^1], and escaping[^2]. Everything supported 😎

[^1]: Parsing only, PCRE limitation 🤷‍♂️
[^2]: Except `/`, see [Constraints](#constraints) for more details.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: preprocess/78cfc4c7c7c55577)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.4` |   `HEAD ⋯ 9.2.0`   |
|  | `^8.3` |   `HEAD ⋯ 9.2.0`   |

[//]: # (end: preprocess/78cfc4c7c7c55577)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "glob-matcher"}})
[//]: # (start: preprocess/d6214a05487f9759)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/glob-matcher
```

[//]: # (end: preprocess/d6214a05487f9759)

# Usage

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: preprocess/4c2bcd97f5d25b12)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Docs\Examples;

use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\GlobMatcher\Options;
use LastDragon_ru\LaraASP\Dev\App\Example;

// Full-featured
$fullGlob = new GlobMatcher('/**/{a,b,c}.txt');

Example::dump($fullGlob->match('/a.txt'));
Example::dump($fullGlob->match('/a/b/c.txt'));
Example::dump($fullGlob->match('/a/b/d.txt'));

// Without `globstar`
$noGlobstar = new GlobMatcher('/**/{a,b,c}.txt', new Options(globstar: false));

Example::dump($noGlobstar->match('/a.txt'));
Example::dump($noGlobstar->match('/**/a.txt'));

// Escaping
$escaped = new GlobMatcher('/\\*.txt');

Example::dump(GlobMatcher::escape('/*.txt'));
Example::dump($escaped->match('/a.txt'));
Example::dump($escaped->match('/*.txt'));
```

The `$fullGlob->match('/a.txt')` is:

```plain
true
```

The `$fullGlob->match('/a/b/c.txt')` is:

```plain
true
```

The `$fullGlob->match('/a/b/d.txt')` is:

```plain
false
```

The `$noGlobstar->match('/a.txt')` is:

```plain
false
```

The `$noGlobstar->match('/**/a.txt')` is:

```plain
true
```

The `GlobMatcher::escape('/*.txt')` is:

```plain
"/\*.txt"
```

The `$escaped->match('/a.txt')` is:

```plain
false
```

The `$escaped->match('/*.txt')` is:

```plain
true
```

[//]: # (end: preprocess/4c2bcd97f5d25b12)

# Globbing

The [`Glob`][code-links/e4c1e0ff644fe7ca] is used internally by the [`GlobMatcher`][code-links/88f9aa0b4389a374] to parse the glob pattern(s). You can also use it if you, for example, need access to AST.

[include:example]: ./docs/Examples/Glob.php
[//]: # (start: preprocess/2d7110585eb2421d)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Docs\Examples;

use LastDragon_ru\GlobMatcher\Glob\Glob;
use LastDragon_ru\LaraASP\Dev\App\Example;

$glob = new Glob('/**/**/?.txt');

Example::dump((string) $glob->regex);
Example::dump($glob->node);
```

The `(string) $glob->regex` is:

```plain
"#^(?:/)(?:(?:(?<=^|/)(?:(?!\.)(?:(?=.))[^/]*?)(?:(?:/|$)|(?=/|$)))*?)(?:(?!\.)(?:(?=.)(?:[^/])(?:\.txt)))$#us"
```

The `$glob->node` is:

```plain
LastDragon_ru\GlobMatcher\Glob\Ast\GlobNode {
  +children: [
    LastDragon_ru\GlobMatcher\Glob\Ast\SegmentNode {},
    LastDragon_ru\GlobMatcher\Glob\Ast\GlobstarNode {
      +count: 2
    },
    LastDragon_ru\GlobMatcher\Glob\Ast\NameNode {
      +children: [
        LastDragon_ru\GlobMatcher\Glob\Ast\QuestionNode {},
        LastDragon_ru\GlobMatcher\Glob\Ast\StringNode {
          +string: ".txt"
        },
      ]
    },
  ]
}
```

[//]: # (end: preprocess/2d7110585eb2421d)

Available options:

[include:example]: ./src/Glob/Options.php
[//]: # (start: preprocess/71addb8e48170c50)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob;

use LastDragon_ru\GlobMatcher\MatchMode;

readonly class Options {
    public function __construct(
        /**
         * If set, the `**` will match all files and zero or more directories
         * and subdirectories.
         *
         * The same as `globstar`.
         */
        public bool $globstar = true,
        /**
         * Enables extended globbing (`?(pattern-list)`, etc).
         *
         * The same as `extglob`.
         */
        public bool $extended = true,
        /**
         * Filenames beginning with a dot are hidden and not matched by default
         * unless the glob begins with a dot or this option set to `true`.
         *
         * The same as `dotglob`.
         */
        public bool $hidden = false,
        public MatchMode $matchMode = MatchMode::Match,
        /**
         * The same as `nocasematch`.
         */
        public bool $matchCase = true,
    ) {
        // empty
    }
}
```

[//]: # (end: preprocess/71addb8e48170c50)

# Brace expansion

You can also expand braces without globbing:

[include:example]: ./docs/Examples/Braces.php
[//]: # (start: preprocess/4d7a2ea77b014c18)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Docs\Examples;

use LastDragon_ru\GlobMatcher\BraceExpander\BraceExpander;
use LastDragon_ru\LaraASP\Dev\App\Example;

use function iterator_to_array;

$expander = new BraceExpander('{a,{0..10..2},c}.txt');

Example::dump(iterator_to_array($expander));
Example::dump($expander->node);
```

The `iterator_to_array($expander)` is:

```plain
[
  "a.txt",
  "0.txt",
  "2.txt",
  "4.txt",
  "6.txt",
  "8.txt",
  "10.txt",
  "c.txt",
]
```

The `$expander->node` is:

```plain
LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode {
  +children: [
    LastDragon_ru\GlobMatcher\BraceExpander\Ast\SequenceNode {
      +children: [
        LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode {
          +children: [
            LastDragon_ru\GlobMatcher\BraceExpander\Ast\StringNode {
              +string: "a"
            },
          ]
        },
        LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode {
          +children: [
            LastDragon_ru\GlobMatcher\BraceExpander\Ast\IntegerSequenceNode {
              +start: "0"
              +end: "10"
              +increment: 2
            },
          ]
        },
        LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode {
          +children: [
            LastDragon_ru\GlobMatcher\BraceExpander\Ast\StringNode {
              +string: "c"
            },
          ]
        },
      ]
    },
    LastDragon_ru\GlobMatcher\BraceExpander\Ast\StringNode {
      +string: ".txt"
    },
  ]
}
```

[//]: # (end: preprocess/4d7a2ea77b014c18)

# Constraints

We are using PCRE to match and the [`lastdragon-ru/text-parser`](../text-parser/README.md) package to parse glob patterns. Both are limits encoding to `UTF-8` only.

Path always checks as is. Unlike bash, there is no special processing of quotes/parentheses inside the pattern.

Only `/` allowed as a path separator. The `\` used by Windows is not supported (it is used as an escape character).

The `\` is always used as an escape character, so `[\b]` will be treated as `[b]` (`\` is gone), `[\\b]` should be used instead.

The `/`, `.` and `..` always match explicitly. Thus, the `a/**` will not match `a`, but will `a/` (slightly different from bash). Also, the `/` cannot be escaped and should not be inside the character class, extended pattern, etc. This means that, e.g. `[a/b]` will be parsed as `[a`, `/`, `b]` and not as characters `a/b`.

# Gratitude

Huge thanks to [micromatch](https://github.com/micromatch/) and especially [picomatch](https://github.com/micromatch/picomatch/) project for a vast set of tests of all features of glob.

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: preprocess/c4ba75080f5a48b7)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

Please use the [main repository](https://github.com/LastDragon-ru/php-packages) to [report issues](https://github.com/LastDragon-ru/php-packages/issues), send [pull requests](https://github.com/LastDragon-ru/php-packages/pulls), or [ask questions](https://github.com/LastDragon-ru/php-packages/discussions).

[//]: # (end: preprocess/c4ba75080f5a48b7)

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/88f9aa0b4389a374]: src/GlobMatcher.php
    "\LastDragon_ru\GlobMatcher\GlobMatcher"

[code-links/e4c1e0ff644fe7ca]: src/Glob/Glob.php
    "\LastDragon_ru\GlobMatcher\Glob\Glob"

[//]: # (end: code-links)
