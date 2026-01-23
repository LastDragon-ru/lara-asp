# PHPUnit Extensions 🐝

Various useful assertions/extensions for PHPUnit.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: preprocess/78cfc4c7c7c55577)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.4` |  `HEAD`  ,  `10.0.0`   |
|  | `^8.3` |  `HEAD`  ,  `10.0.0`   |
|  PHPUnit  | `^12.0.0` |  `HEAD`  ,  `10.0.0`   |
|  | `^11.3.0` |  `HEAD`   |
|  | `^11.2.0` |  `10.0.0`   |

[//]: # (end: preprocess/78cfc4c7c7c55577)

[include:template]: ../../docs/Shared/InstallationDev.md ({"data": {"package": "phpunit-extensions"}})
[//]: # (start: preprocess/d6ee4eda354f4977)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

> [!NOTE]
>
> The package intended to use in dev.

```shell
composer require --dev lastdragon-ru/phpunit-extensions
```

[//]: # (end: preprocess/d6ee4eda354f4977)

# Extensions

## Strict Scalar Compare

By default, PHPUnit compares scalars via `==` operator, so `Assert::assertEquals(1, true)` will pass. The extension adds own [`Comparator`][code-links/62e94c1c0fe743de] to compare scalars via `===` operator.

To [register extension](https://docs.phpunit.de/en/12.5/extending-phpunit.html#registering-an-extension-from-a-composer-package) update your `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <!-- ... -->
    <extensions>
        <bootstrap class="\LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare\Extension"/>
    </extensions>
    <!-- ... -->
</phpunit>
```

## Test Requirements

PHPUnit provides several attributes like `\PHPUnit\Framework\Attributes\RequiresPhp` that allow skip the test if the condition doesn't meet. But there is no way to check if the specific composer package is installed or not. The extension fills this gap.

To [register extension](https://docs.phpunit.de/en/12.5/extending-phpunit.html#registering-an-extension-from-a-composer-package) update your `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <!-- ... -->
    <extensions>
        <bootstrap class="\LastDragon_ru\PhpUnit\Extensions\Requirements\Extension"/>
    </extensions>
    <!-- ... -->
</phpunit>
```

[include:example]: ./docs/Examples/RequirementsExtensionTest.php
[//]: # (start: preprocess/a201179110b693b9)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Docs\Examples;

use Composer\InstalledVersions;
use LastDragon_ru\PhpUnit\Extensions\Requirements\Attributes\RequiresPackage;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[RequiresPackage('phpunit/phpunit')]
final class RequirementsExtensionTest extends TestCase {
    #[RequiresPackage('phpunit/phpunit', '>=10.0.0')]
    public function testSomething(): void {
        self::assertTrue(InstalledVersions::isInstalled('phpunit/phpunit'));
    }
}
```

[//]: # (end: preprocess/a201179110b693b9)

# Assertions

> [!NOTE]
> You can include all available assertions via `use` [`\LastDragon_ru\PhpUnit\Assertions`][code-links/8ddbbc27bf46e25a] or only needed via individual traits like [`\LastDragon_ru\PhpUnit\Filesystem\Assertions`][code-links/d3222cbf284d2c91].

* [`\LastDragon_ru\PhpUnit\Filesystem\Assertions::assertDirectoryEquals()`][code-links/c6a20e117a6f2c45] - Asserts that Directory equals Directory.
* [`\LastDragon_ru\PhpUnit\Filesystem\Assertions::assertDirectoryEmpty()`][code-links/faed13c93f9ed240] - Asserts that Directory empty.

# Constraints

## [`DirectoryEquals`][code-links/e645f74c9dd01bc1]

[include:docblock]: src/Filesystem/Constraints/DirectoryEquals.php
[//]: # (start: preprocess/280bafbe4f4b8167)
[//]: # (warning: Generated automatically. Do not edit.)

Compares two directories. By default, directories are equal if the list of
their children is the same, and files have the same content. Permissions are
ignored. You can override `\self::properties()` and `\self::equal()`
to customize comparison logic.

[//]: # (end: preprocess/280bafbe4f4b8167)

# Utilities

## [`TestData`][code-links/ded4ad00d1ea1842]

[include:docblock]: src/Utils/TestData.php
[//]: # (start: preprocess/882c71f1b5a04671)
[//]: # (warning: Generated automatically. Do not edit.)

Small helper to load data associated with test.

[//]: # (end: preprocess/882c71f1b5a04671)

## [`TempFile`][code-links/2ed0bfaade389715]

[include:docblock]: src/Utils/TempFile.php
[//]: # (start: preprocess/4daefce210284202)
[//]: # (warning: Generated automatically. Do not edit.)

Creates a temporary file in the system temp directory. The file will be
removed after the instance removal.

[//]: # (end: preprocess/4daefce210284202)

## [`TempDirectory`][code-links/988d0b3180c21a3f]

[include:docblock]: src/Utils/TempDirectory.php
[//]: # (start: preprocess/337f8e1f2ed8e4ba)
[//]: # (warning: Generated automatically. Do not edit.)

Creates a temporary directory in the system temp directory. The directory will
be removed after the instance removal.

[//]: # (end: preprocess/337f8e1f2ed8e4ba)

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

[code-links/8ddbbc27bf46e25a]: src/Assertions.php
    "\LastDragon_ru\PhpUnit\Assertions"

[code-links/62e94c1c0fe743de]: src/Extensions/StrictScalarCompare/Comparator.php
    "\LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare\Comparator"

[code-links/d3222cbf284d2c91]: src/Filesystem/Assertions.php
    "\LastDragon_ru\PhpUnit\Filesystem\Assertions"

[code-links/faed13c93f9ed240]: src/Filesystem/Assertions.php#L34-L47
    "\LastDragon_ru\PhpUnit\Filesystem\Assertions::assertDirectoryEmpty()"

[code-links/c6a20e117a6f2c45]: src/Filesystem/Assertions.php#L16-L32
    "\LastDragon_ru\PhpUnit\Filesystem\Assertions::assertDirectoryEquals()"

[code-links/e645f74c9dd01bc1]: src/Filesystem/Constraints/DirectoryEquals.php
    "\LastDragon_ru\PhpUnit\Filesystem\Constraints\DirectoryEquals"

[code-links/988d0b3180c21a3f]: src/Utils/TempDirectory.php
    "\LastDragon_ru\PhpUnit\Utils\TempDirectory"

[code-links/2ed0bfaade389715]: src/Utils/TempFile.php
    "\LastDragon_ru\PhpUnit\Utils\TempFile"

[code-links/ded4ad00d1ea1842]: src/Utils/TestData.php
    "\LastDragon_ru\PhpUnit\Utils\TestData"

[//]: # (end: code-links)
