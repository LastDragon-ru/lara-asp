# GraphQL Printer

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema and Queries in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema/Query.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: 0f999169cbabc32d4f47c79c31d74f8b4066c685962719bae5df3c63a08ea382)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 3.0.0`   |
|  | `^8.1` |   `6.4.1 ⋯ 3.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 3.0.0`   |
|  `webonyx/graphql-php`  | `^15.4.0` |   `HEAD ⋯ 4.2.1`   |
|  | `^15.2.4` |   `4.2.0 ⋯ 4.0.0`   |
|  | `^14.11.9` |  `3.0.0`   |

[//]: # (end: 0f999169cbabc32d4f47c79c31d74f8b4066c685962719bae5df3c63a08ea382)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "graphql-printer"}})
[//]: # (start: 173e32825c9e0296239282894ed3784c959423efa063826d9806fc9fa8b91675)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-graphql-printer
```

[//]: # (end: 173e32825c9e0296239282894ed3784c959423efa063826d9806fc9fa8b91675)

# Usage

There are two primary methods: `Printer::print()` and `Printer::export()`. The `print()` will print the current type only, meanwhile `export()` will print the current type and all used types/directives:

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: 09d4b0171aeb5e738bed588b155864570d400f5a1aa8c592a289ae3708188cdf)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;

$schema   = BuildSchema::build(
    <<<'GRAPHQL'
    type Query {
        a: A
    }

    type A @a {
        id: ID!
        b: [B!]
    }

    type B @b {
        id: ID!
    }

    directive @a on OBJECT
    directive @b on OBJECT
    GRAPHQL,
);
$type     = $schema->getType('A');
$settings = new DefaultSettings();
$printer  = new Printer($settings, null, $schema);

assert($type !== null);

Example::raw($printer->print($type), 'graphql');
Example::raw($printer->export($type), 'graphql');
```

The `$printer->print($type)` is:

```graphql
type A
@a
{
    b: [B!]
    id: ID!
}
```

The `$printer->export($type)` is:

```graphql
type A
@a
{
    b: [B!]
    id: ID!
}

directive @a
on
    | OBJECT

directive @b
on
    | OBJECT

type B
@b
{
    id: ID!
}
```

[//]: # (end: 09d4b0171aeb5e738bed588b155864570d400f5a1aa8c592a289ae3708188cdf)

# Customization

Please see:

* [`Settings`](./src/Settings) directory to see built-in settings;
* [`Settings`](./src/Contracts/Settings.php) interface to see all supported settings;
* [`DirectiveResolver`](./src/Contracts/DirectiveResolver.php) interface to define your own way to find all available directives and their definitions;

# Filtering

> [!NOTE]
>
> By default, built-in/internal type/directives are not printed, if you want/need them, you should allow them by type/directive definitions filters.

The Printer allows filter out types and directives. This may be useful to exclude them from the schema completely. Filtering also works for queries. Please note that types filtering will work only if the schema is known (the schema is required to determine the type of argument nodes). For some AST node types, their type may also be required.

[include:example]: ./docs/Examples/Filtering.php
[//]: # (start: 6688f939261e38bcd99f9b5b3dde76656bf46cfed74a99dd23a54d69eafe24e6)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use GraphQL\Language\Parser;
use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\TypeFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;

$typeFilter      = new class() implements TypeFilter {
    #[Override]
    public function isAllowedType(string $type, bool $isStandard): bool {
        return $type !== 'Forbidden';
    }
};
$directiveFilter = new class() implements DirectiveFilter {
    #[Override]
    public function isAllowedDirective(string $directive, bool $isStandard): bool {
        return $directive !== 'forbidden';
    }
};

$schema = BuildSchema::build(
    <<<'GRAPHQL'
    type Query {
        allowed: Boolean @forbidden @allowed
        forbidden: Forbidden
    }

    type Forbidden {
        id: ID!
    }

    directive @allowed on FIELD_DEFINITION
    directive @forbidden on FIELD_DEFINITION
    GRAPHQL,
);
$query  = Parser::parse(
    <<<'GRAPHQL'
    query {
        allowed
        forbidden {
            id
        }
    }
    GRAPHQL,
);

$settings = (new DefaultSettings())
    ->setDirectiveFilter($directiveFilter)
    ->setTypeFilter($typeFilter);
$printer  = new Printer($settings, null, $schema);

Example::raw($printer->print($schema), 'graphql');
Example::raw($printer->print($query), 'graphql');
```

The `$printer->print($schema)` is:

```graphql
directive @allowed
on
    | FIELD_DEFINITION

type Query {
    allowed: Boolean
    @allowed
}
```

The `$printer->print($query)` is:

```graphql
query {
    allowed
}
```

[//]: # (end: 6688f939261e38bcd99f9b5b3dde76656bf46cfed74a99dd23a54d69eafe24e6)

# Laravel/Lighthouse

It is highly recommended to use [`lara-asp-graphql`](../graphql/README.md#Printer) package to use the `Printer` within the Laravel/Lighthouse application.

# Testing Assertions

[include:document-list]: ./docs/Assertions
[//]: # (start: c9953bb428d837e4a82f61878dcfa1a88fc32adcfc3e683dcc228578acec584b)
[//]: # (warning: Generated automatically. Do not edit.)

## `assertGraphQLExportableEquals`

Exports and compares two GraphQL schemas/types/nodes/etc.

[Read more](<docs/Assertions/AssertGraphQLExportableEquals.md>).

## `assertGraphQLPrintableEquals`

Prints and compares two GraphQL schemas/types/nodes/etc.

[Read more](<docs/Assertions/AssertGraphQLPrintableEquals.md>).

[//]: # (end: c9953bb428d837e4a82f61878dcfa1a88fc32adcfc3e683dcc228578acec584b)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: fc88f84f187016cb8144e9a024844024492f0c3a5a6f8d128bf69a5814cc8cc5)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: fc88f84f187016cb8144e9a024844024492f0c3a5a6f8d128bf69a5814cc8cc5)
