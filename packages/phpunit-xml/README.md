# PHPUnit XML Assertions 🐝

PHPUnit XML / XML Schema assertions.

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

[include:template]: ../../docs/Shared/InstallationDev.md ({"data": {"package": "phpunit-xml"}})
[//]: # (start: preprocess/216ee94121bcd5a9)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

> [!NOTE]
>
> The package intended to use in dev.

```shell
composer require --dev lastdragon-ru/phpunit-xml
```

[//]: # (end: preprocess/216ee94121bcd5a9)

# Usage

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: preprocess/4c2bcd97f5d25b12)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Xml\Docs\Examples;

use LastDragon_ru\PhpUnit\Xml\Assertions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class Usage extends TestCase {
    use Assertions;
}
```

[//]: # (end: preprocess/4c2bcd97f5d25b12)

# Assertions

* [`Assertions::assertXmlMatchesSchema()`][code-links/ac544120a9c38590] - Asserts that XML matches schema.

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: preprocess/c4ba75080f5a48b7)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

Please use the [main repository](https://github.com/LastDragon-ru/php-packages) to [report issues](https://github.com/LastDragon-ru/php-packages/issues), send [pull requests](https://github.com/LastDragon-ru/php-packages/pulls), or [ask questions](https://github.com/LastDragon-ru/php-packages/discussions).

[//]: # (end: preprocess/c4ba75080f5a48b7)

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/ac544120a9c38590]: src/Assertions.php#L14-L31
    "\LastDragon_ru\PhpUnit\Xml\Assertions::assertXmlMatchesSchema()"

[//]: # (end: code-links)
