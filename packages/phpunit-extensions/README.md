# PHPUnit Extensions 🐝

Various useful assertions/extensions for PHPUnit.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: preprocess/78cfc4c7c7c55577)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.4` |  `HEAD`   |
|  | `^8.3` |  `HEAD`   |
|  PHPUnit  | `^12.0.0` |  `HEAD`   |
|  | `^11.2.0` |  `HEAD`   |

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

[code-links/62e94c1c0fe743de]: src/Extensions/StrictScalarCompare/Comparator.php
    "\LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare\Comparator"

[//]: # (end: code-links)
