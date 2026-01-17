# (Laravel) GraphQL Testing Assertions for Laravel/Lighthouse 🐝

Useful assertions for PHPUnit to check printed/exported type/queries and more with [`lastdragon-ru/lara-asp-graphql`](../lara-asp-graphql/README.md) package

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

[include:template]: ../../docs/Shared/InstallationDev.md ({"data": {"package": "lara-asp-graphql-testing"}})
[//]: # (start: preprocess/e65e38ce4f015702)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

> [!NOTE]
>
> The package intended to use in dev.

```shell
composer require --dev lastdragon-ru/lara-asp-graphql-testing
```

[//]: # (end: preprocess/e65e38ce4f015702)

# Usage

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: preprocess/4c2bcd97f5d25b12)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Docs\Examples;

use LastDragon_ru\LaraASP\GraphQL\Testing\Assertions;
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

[include:document-list]: ./docs/Assertions
[//]: # (start: preprocess/c79a463462fd8331)
[//]: # (warning: Generated automatically. Do not edit.)

## [`assertGraphQLIntrospectionEquals`](<docs/Assertions/AssertGraphQLIntrospectionEquals.md>)

Compares default public schema (as the client sees it through introspection).

[Read more](<docs/Assertions/AssertGraphQLIntrospectionEquals.md>).

## [`assertGraphQLSchemaEquals`](<docs/Assertions/AssertGraphQLSchemaEquals.md>)

Compares default internal schema (with all directives).

[Read more](<docs/Assertions/AssertGraphQLSchemaEquals.md>).

## [`assertGraphQLSchemaNoBreakingChanges`](<docs/Assertions/AssertGraphQLSchemaNoBreakingChanges.md>)

Checks that no breaking changes in the default internal schema (with all directives).

[Read more](<docs/Assertions/AssertGraphQLSchemaNoBreakingChanges.md>).

## [`assertGraphQLSchemaNoDangerousChanges`](<docs/Assertions/AssertGraphQLSchemaNoDangerousChanges.md>)

Checks that no dangerous changes in the default internal schema (with all directives).

[Read more](<docs/Assertions/AssertGraphQLSchemaNoDangerousChanges.md>).

## [`assertGraphQLSchemaValid`](<docs/Assertions/AssertGraphQLSchemaValid.md>)

Validates default internal schema (with all directives). Faster than `lighthouse:validate-schema` command because loads only used directives.

[Read more](<docs/Assertions/AssertGraphQLSchemaValid.md>).

[//]: # (end: preprocess/c79a463462fd8331)

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: preprocess/c4ba75080f5a48b7)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

Please use the [main repository](https://github.com/LastDragon-ru/php-packages) to [report issues](https://github.com/LastDragon-ru/php-packages/issues), send [pull requests](https://github.com/LastDragon-ru/php-packages/pulls), or [ask questions](https://github.com/LastDragon-ru/php-packages/discussions).

[//]: # (end: preprocess/c4ba75080f5a48b7)
