# GraphQL Printer

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema and Queries in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema/Query.

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 7345502de8e33b9f2179e1d5e492a19bdc4b3d1012d77ee610aa6205dad3530b)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.2` |   `HEAD ⋯ 3.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 3.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 3.0.0`   |
|  `webonyx/graphql-php`  | `^15.4.0` |   `HEAD ⋯ 4.2.1`   |
|  | `^15.2.4` |   `4.2.0 ⋯ 4.0.0`   |
|  | `^14.11.9` |  `3.0.0`   |

[//]: # (end: 7345502de8e33b9f2179e1d5e492a19bdc4b3d1012d77ee610aa6205dad3530b)

# Installation

```shell
composer require lastdragon-ru/lara-asp-graphql-printer
```

## Usage

There are two primary methods: `Printer::print()` and `Printer::export()`. The `print()` will print the current type only, meanwhile `export()` will print the current type and all used types/directives:

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: caf4823c1825389ee306092d37b26a07d291dc0264dff1977be86e61ca455a97)
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
directive @a
on
    | OBJECT

directive @b
on
    | OBJECT

type A
@a
{
    b: [B!]
    id: ID!
}

type B
@b
{
    id: ID!
}
```

[//]: # (end: caf4823c1825389ee306092d37b26a07d291dc0264dff1977be86e61ca455a97)

## Customization

Please see:

* [`Settings`](./src/Settings) directory to see built-in settings;
* [`Settings`](./src/Contracts/Settings.php) interface to see all supported settings;
* [`DirectiveResolver`](./src/Contracts/DirectiveResolver.php) interface to define your own way to find all available directives and their definitions;

## Filtering

The Printer allows filter out types and directives. This may be useful to exclude them from the schema completely. Filtering also works for queries. Please note that types filtering will work only if the schema is known (the schema is required to determine the type of argument nodes). For some AST node types, their type may also be required.

[include:example]: ./docs/Examples/Filtering.php
[//]: # (start: 7e59d349ffa361a16d73a13265a5c38cc47675983fcce63a74fe71148953052f)
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
    public function isAllowedType(string $type, bool $isStandard): bool {
        return $type !== 'Forbidden';
    }
};
$directiveFilter = new class() implements DirectiveFilter {
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

[//]: # (end: 7e59d349ffa361a16d73a13265a5c38cc47675983fcce63a74fe71148953052f)

## Laravel/Lighthouse

It is highly recommended to use [`lara-asp-graphql`][pkg:graphql#Printer] package to use the `Printer` within the Laravel/Lighthouse application.

## Testing Assertions

Package also provides few great [GraphQL Assertions](./src/Testing/GraphQLAssertions.php):

| Name                            | Description                                               |
|---------------------------------|-----------------------------------------------------------|
| `assertGraphQLPrintableEquals`  | Prints and compares two GraphQL schemas/types/nodes/etc.  |
| `assertGraphQLExportableEquals` | Exports and compares two GraphQL schemas/types/nodes/etc. |

[include:file]: ../../docs/shared/Contributing.md
[//]: # (start: 0001ad9d31b5a203286c531c6880292795cb49f2074223b60ae12c6faa6c42eb)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 0001ad9d31b5a203286c531c6880292795cb49f2074223b60ae12c6faa6c42eb)

[include:file]: ../../docs/shared/Links.md
[//]: # (start: d8baa2418c8dbf3ba09f9b223885c4326bee3e69a2dc0873e243f0d34e002a85)
[//]: # (warning: Generated automatically. Do not edit.)

[pkg:graphql#Printer]: https://github.com/LastDragon-ru/lara-asp/tree/main/packages/graphql#Printer

[//]: # (end: d8baa2418c8dbf3ba09f9b223885c4326bee3e69a2dc0873e243f0d34e002a85)
