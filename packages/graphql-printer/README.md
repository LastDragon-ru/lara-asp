# GraphQL Printer

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema and Queries in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema/Query.

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 196f435a1c8bc8d5966e42b9fd090d5ccc17c75206e617d7f8369cd9328846ea)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 3.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 3.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 3.0.0`   |
|  `webonyx/graphql-php`  | `^15.4.0` |   `HEAD ⋯ 4.2.1`   |
|  | `^15.2.4` |   `4.2.0 ⋯ 4.0.0`   |
|  | `^14.11.9` |  `3.0.0`   |

[//]: # (end: 196f435a1c8bc8d5966e42b9fd090d5ccc17c75206e617d7f8369cd9328846ea)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "graphql-printer"}})
[//]: # (start: dcf3043aff3a50970117872a9bba432cb3ef84a034a0fc88bcdc6d9dcae2ec06)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-graphql-printer
```

[//]: # (end: dcf3043aff3a50970117872a9bba432cb3ef84a034a0fc88bcdc6d9dcae2ec06)

# Usage

There are two primary methods: `Printer::print()` and `Printer::export()`. The `print()` will print the current type only, meanwhile `export()` will print the current type and all used types/directives:

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: c709a4b715d1bde109a5d27982a2a5d6f481b5c72338e162e394ccbb6fc9208a)
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

[//]: # (end: c709a4b715d1bde109a5d27982a2a5d6f481b5c72338e162e394ccbb6fc9208a)

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
[//]: # (start: 963f066509a17e2999eaa0fa940bd0f608ad2fc212eb6511f11b69eae9f7478e)
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

[//]: # (end: 963f066509a17e2999eaa0fa940bd0f608ad2fc212eb6511f11b69eae9f7478e)

# Laravel/Lighthouse

It is highly recommended to use [`lara-asp-graphql`][pkg:graphql#Printer] package to use the `Printer` within the Laravel/Lighthouse application.

# Testing Assertions

[include:document-list]: ./docs/Assertions
[//]: # (start: 86d73ad55f2c494dfe35350837400088c82dfa7457eafd0d30392ba96bbbdc9a)
[//]: # (warning: Generated automatically. Do not edit.)

## `assertGraphQLExportableEquals`

Exports and compares two GraphQL schemas/types/nodes/etc.

[Read more](<docs/Assertions/AssertGraphQLExportableEquals.md>).

## `assertGraphQLPrintableEquals`

Prints and compares two GraphQL schemas/types/nodes/etc.

[Read more](<docs/Assertions/AssertGraphQLPrintableEquals.md>).

[//]: # (end: 86d73ad55f2c494dfe35350837400088c82dfa7457eafd0d30392ba96bbbdc9a)

[include:file]: ../../docs/Shared/Upgrading.md
[//]: # (start: 3c3826915e1d570b3982fdc6acf484950f0add7bb09d71c8c99b4a0e0fc5b43a)
[//]: # (warning: Generated automatically. Do not edit.)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[//]: # (end: 3c3826915e1d570b3982fdc6acf484950f0add7bb09d71c8c99b4a0e0fc5b43a)

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: 6b81b030ae74b2d149ec76cbec1b053da8da4e0ac4fd865f560548f3ead955e8)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 6b81b030ae74b2d149ec76cbec1b053da8da4e0ac4fd865f560548f3ead955e8)

[include:file]: ../../docs/Shared/Links.md
[//]: # (start: 9ac5c57eb03fcabb221c8db950c2dc20215f17f6e4ab17fd3a5def405da61f91)
[//]: # (warning: Generated automatically. Do not edit.)

[pkg:graphql#Printer]: https://github.com/LastDragon-ru/lara-asp/tree/main/packages/graphql#Printer

[//]: # (end: 9ac5c57eb03fcabb221c8db950c2dc20215f17f6e4ab17fd3a5def405da61f91)
