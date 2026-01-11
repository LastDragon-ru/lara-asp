# GraphQL Testing Assertions 🐝

Useful assertions for PHPUnit to check printed/exported type/queries with [`lastdragon-ru/graphql-printer`](../graphql-printer/README.md) package

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

[include:template]: ../../docs/Shared/InstallationDev.md ({"data": {"package": "phpunit-graphql"}})
[//]: # (start: preprocess/d713038c45b11d62)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

> [!NOTE]
>
> The package intended to use in dev.

```shell
composer require --dev lastdragon-ru/phpunit-graphql
```

[//]: # (end: preprocess/d713038c45b11d62)

# Usage

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: preprocess/4c2bcd97f5d25b12)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\GraphQL\Docs\Examples;

use LastDragon_ru\PhpUnit\GraphQL\GraphQLAssertions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class Usage extends TestCase {
    use GraphQLAssertions;
}
```

[//]: # (end: preprocess/4c2bcd97f5d25b12)

# Laravel/Lighthouse

It is highly recommended to use [`lastdragon-ru/lara-asp-graphql-testing`](../lara-asp-graphql-testing/README.md) package to use assertions within the Laravel/Lighthouse application.

# Assertions

[include:document-list]: ./docs/Assertions
[//]: # (start: preprocess/c79a463462fd8331)
[//]: # (warning: Generated automatically. Do not edit.)

## [`assertGraphQLExportableEquals`](<docs/Assertions/AssertGraphQLExportableEquals.md>)

Exports and compares two GraphQL schemas/types/nodes/etc.

[Read more](<docs/Assertions/AssertGraphQLExportableEquals.md>).

## [`assertGraphQLPrintableEquals`](<docs/Assertions/AssertGraphQLPrintableEquals.md>)

Prints and compares two GraphQL schemas/types/nodes/etc.

[Read more](<docs/Assertions/AssertGraphQLPrintableEquals.md>).

[//]: # (end: preprocess/c79a463462fd8331)

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: preprocess/c4ba75080f5a48b7)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: preprocess/c4ba75080f5a48b7)
