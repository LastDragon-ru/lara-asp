# Path

Provides utilities for working with file and directory paths in an object-oriented way for all path types.

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

The package works only with paths, not with URL/URI/etc, and doesn't interact with OS. The following table shows possible path types.

| Type                                                   | Example                                | Root                                      |
|--------------------------------------------------------|----------------------------------------|-------------------------------------------|
| [`Type::Absolute`][code-links/eeda5588c3cf02ff]        | `/path`                                | `/`                                       |
| [`Type::Relative`][code-links/5f48ca28e823cf62]        | `path`, `./path`, `../path`            | `​`                                       |
| [`Type::Home`][code-links/0b2589a0ba046f5b]            | `~`, `~/`, `~/path`                    | `~/`                                      |
| [`Type::User`][code-links/d6e7f9ebd35ee97e]            | `~username`, `~username/path`          | `~username/`                              |
| [`Type::Unc`][code-links/338940468184c4b5]             | `\\ComputerName\SharedFolder\Resource` | `\\ComputerName\SharedFolder`             |
| [`Type::WindowsAbsolute`][code-links/bad0bc31946fc486] | `C:\path`                              | `C:\`                                     |
| [`Type::WindowsRelative`][code-links/d77252dc90c836dd] | `C:path`                               | `C:/<current directory>`[^1] or `C:\`[^1] |

[^1]: <https://learn.microsoft.com/en-us/dotnet/standard/io/file-path-formats>

As a path separator, any/mix of `/`/`\` can be used, but in the normalized form all `\` will always be converted into `/`:

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
$win  = new FilePath('c:/path/../to/../file.txt');

Example::dump((string) $base->resolve($file));
Example::dump((string) $win->normalized());
```

The `(string) $base->resolve($file)` is:

```plain
"/path/to/file.txt"
```

The `(string) $win->normalized()` is:

```plain
"C:/file.txt"
```

[//]: # (end: preprocess/a6f0748f7e17a9e0)

## Home `~/`

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

## Windows

Nothing special here except how the relative path resolves[^1]:

[include:example]: ./docs/Examples/Windows.php
[//]: # (start: preprocess/392556a3996e57eb)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$base = new DirectoryPath('C:/path');

Example::dump(
    (string) $base->resolve(new FilePath('C:file.txt')),
);

Example::dump(
    (string) $base->resolve(new FilePath('D:file.txt')),
);
```

The `(string) $base->resolve(new FilePath('C:file.txt'))` is:

```plain
"C:/path/file.txt"
```

The `(string) $base->resolve(new FilePath('D:file.txt'))` is:

```plain
"D:/file.txt"
```

[//]: # (end: preprocess/392556a3996e57eb)

## Universal Naming Convention (UNC)

In [UNC](https://en.wikipedia.org/wiki/Path_(computing)#UNC), the root is `\\server\share`, so relative paths resolve based on it:

[include:example]: ./docs/Examples/Unc.php
[//]: # (start: preprocess/880c01870a9b887d)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$base = new DirectoryPath('//server/share/directory');
$file = new FilePath('../../../../../file.txt');

Example::dump((string) $base->resolve($file));
```

The `(string) $base->resolve($file)` is:

```plain
"//server/share/file.txt"
```

[//]: # (end: preprocess/880c01870a9b887d)

## Making path relative

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

[code-links/eeda5588c3cf02ff]: src/Type.php#L6-L9
    "\LastDragon_ru\Path\Type::Absolute"

[code-links/0b2589a0ba046f5b]: src/Type.php#L20-L23
    "\LastDragon_ru\Path\Type::Home"

[code-links/5f48ca28e823cf62]: src/Type.php#L10-L13
    "\LastDragon_ru\Path\Type::Relative"

[code-links/338940468184c4b5]: src/Type.php#L14-L19
    "\LastDragon_ru\Path\Type::Unc"

[code-links/d6e7f9ebd35ee97e]: src/Type.php#L24-L27
    "\LastDragon_ru\Path\Type::User"

[code-links/bad0bc31946fc486]: src/Type.php#L28-L31
    "\LastDragon_ru\Path\Type::WindowsAbsolute"

[code-links/d77252dc90c836dd]: src/Type.php#L32-L37
    "\LastDragon_ru\Path\Type::WindowsRelative"

[//]: # (end: code-links)
