# Path

Provides utilities for working with file and directory paths in an object-oriented way.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: preprocess/78cfc4c7c7c55577)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.4` |  `HEAD`  ,  `9.2.0`   |
|  | `^8.3` |  `HEAD`  ,  `9.2.0`   |

[//]: # (end: preprocess/78cfc4c7c7c55577)

# Installation

```shell
composer require lastdragon-ru/path
```

# Motivation

Most similar packages consider file/directory paths as strings. It is work until we need to modify and/or actively work with them. Relative path resolution depends on the type of base path, for example:

* `/path/to/directory/file.md` + `../file.txt` = `/path/to/file.txt`
* `/path/to/directory` + `../file.txt` = `/path/to/file.txt`

The strings (in general case) don't allow us to distinguish the directory path from the file path, and so resolve the path of `file.txt` correctly. Strings also cannot ensure type safety - there is no way to disallow passing a directory path where only a file path is wanted, and vice versa. Etc. To solve all these problems, the package defines [`DirectoryPath`][code-links/eff996e6f7f5e6b3] and [`FilePath`][code-links/43d8e2c832b53052].

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: preprocess/4c2bcd97f5d25b12)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$baseDirectory = new DirectoryPath('/path/to/directory');
$baseFile      = new FilePath('/path/to/directory/file.md');
$file          = new FilePath('../file.txt');

Example::dump((string) $baseDirectory->resolve($file));
Example::dump((string) $baseFile->resolve($file));
Example::dump((string) $baseFile->file('../../file.md'));
```

The `(string) $baseDirectory->resolve($file)` is:

```plain
"/path/to/file.txt"
```

The `(string) $baseFile->resolve($file)` is:

```plain
"/path/to/file.txt"
```

The `(string) $baseFile->file('../../file.md')` is:

```plain
"/path/file.md"
```

[//]: # (end: preprocess/4c2bcd97f5d25b12)

# What is supported

The package works only with paths, not with URL/URI/etc, and doesn't interact with OS. As a path separator, any/mix of `/`/`\\` can be used, but in the normalized form all `\\` will always be converted into `/`:

[include:example]: ./docs/Examples/Normalization.php
[//]: # (start: preprocess/a6f0748f7e17a9e0)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$base = new DirectoryPath('\\path\\.\\to\\directory');
$file = new FilePath('../path/../to/../file.txt');

Example::dump((string) $base->resolve($file));
```

The `(string) $base->resolve($file)` is:

```plain
"/path/to/file.txt"
```

[//]: # (end: preprocess/a6f0748f7e17a9e0)

OS independent means that unlike e.g. `\Symfony\Component\Filesystem\Path` the user home directory `~/` will not be replaced to the actual path, moreover paths stated with `~/` will be treatment like absolute paths (because in almost all cases it is an absolute path).

[include:example]: ./docs/Examples/Home.php
[//]: # (start: preprocess/e29c126d3008f205)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$home = new DirectoryPath('~/path');
$file = new FilePath('file.txt');

Example::dump($home->type);
Example::dump((string) $home->resolve($file));
Example::dump((string) $home->file('../../../file.md')); // !
```

The `$home->type` is:

```plain
LastDragon_ru\Path\Type {
  +name: "Home"
  +value: "~?"
}
```

The `(string) $home->resolve($file)` is:

```plain
"~/path/file.txt"
```

The `(string) $home->file('../../../file.md')` is:

```plain
"~/file.md"
```

[//]: # (end: preprocess/e29c126d3008f205)

In addition to the user home path, the package also has basic support of [Universal Naming Convention (UNC)](https://en.wikipedia.org/wiki/Path_(computing)#UNC) format - if the path starts with `//` (or `\\`) it treat as UNC:

[include:example]: ./docs/Examples/Unc.php
[//]: # (start: preprocess/880c01870a9b887d)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\FilePath;

Example::dump(
    (new FilePath('//server/share/path/to/file.txt'))->type,
);
```

The `(new FilePath('//server/share/path/to/file.txt'))->type` is:

```plain
LastDragon_ru\Path\Type {
  +name: "Unc"
  +value: "//"
}
```

[//]: # (end: preprocess/880c01870a9b887d)

One of notable cases where the type of path matters:

[include:example]: ./docs/Examples/Relative.php
[//]: # (start: preprocess/a3135a42fea85bd0)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$base = new DirectoryPath('~/path/to/directory');

Example::dump((string) $base->relative(new FilePath('~/file.txt')));
Example::dump($base->relative(new FilePath('/file.txt'))); // `null`, because type differ
```

The `(string) $base->relative(new FilePath('~/file.txt'))` is:

```plain
"../../../file.txt"
```

The `$base->relative(new FilePath('/file.txt'))` is:

```plain
null
```

[//]: # (end: preprocess/a3135a42fea85bd0)

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

[code-links/eff996e6f7f5e6b3]: src/DirectoryPath.php
    "\LastDragon_ru\Path\DirectoryPath"

[code-links/43d8e2c832b53052]: src/FilePath.php
    "\LastDragon_ru\Path\FilePath"

[//]: # (end: code-links)
