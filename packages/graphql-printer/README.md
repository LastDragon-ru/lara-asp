# GraphQL Printer

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema and Queries in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema/Query.

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)
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

[//]: # (end: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "graphql-printer"}})
[//]: # (start: dcf3043aff3a50970117872a9bba432cb3ef84a034a0fc88bcdc6d9dcae2ec06)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-graphql-printer
```

[//]: # (end: dcf3043aff3a50970117872a9bba432cb3ef84a034a0fc88bcdc6d9dcae2ec06)

## Usage

There are two primary methods: `Printer::print()` and `Printer::export()`. The `print()` will print the current type only, meanwhile `export()` will print the current type and all used types/directives:

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: 25c8cf0ee2862aeda3cd8ff6bf8d2d3592fee1c00042550be5ee7686ead4cc44)
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

[//]: # (end: 25c8cf0ee2862aeda3cd8ff6bf8d2d3592fee1c00042550be5ee7686ead4cc44)

## Customization

Please see:

* [`Settings`](./src/Settings) directory to see built-in settings;
* [`Settings`](./src/Contracts/Settings.php) interface to see all supported settings;
* [`DirectiveResolver`](./src/Contracts/DirectiveResolver.php) interface to define your own way to find all available directives and their definitions;

## Filtering

The Printer allows filter out types and directives. This may be useful to exclude them from the schema completely. Filtering also works for queries. Please note that types filtering will work only if the schema is known (the schema is required to determine the type of argument nodes). For some AST node types, their type may also be required.

[include:example]: ./docs/Examples/Filtering.php
[//]: # (start: 292c33ba589c3d1a9a6b77c55aad173396a11c5fed1da07af456c995f3f55838)
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

[//]: # (end: 292c33ba589c3d1a9a6b77c55aad173396a11c5fed1da07af456c995f3f55838)

## Laravel/Lighthouse

It is highly recommended to use [`lara-asp-graphql`][pkg:graphql#Printer] package to use the `Printer` within the Laravel/Lighthouse application.

## Testing Assertions

Package also provides few great [GraphQL Assertions](./src/Testing/GraphQLAssertions.php):

| Name                            | Description                                               |
|---------------------------------|-----------------------------------------------------------|
| `assertGraphQLPrintableEquals`  | Prints and compares two GraphQL schemas/types/nodes/etc.  |
| `assertGraphQLExportableEquals` | Exports and compares two GraphQL schemas/types/nodes/etc. |

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: 057ec3a599c54447e95d6dd2e9f0f6a6621d9eb75446a5e5e471ba9b2f414b89)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 057ec3a599c54447e95d6dd2e9f0f6a6621d9eb75446a5e5e471ba9b2f414b89)

[include:file]: ../../docs/Shared/Links.md
[//]: # (start: 6c180b37114202a8766bad1a59a3c0699948cc875617c85fd14a024e3bca64fe)
[//]: # (warning: Generated automatically. Do not edit.)

[pkg:graphql#Printer]: https://github.com/LastDragon-ru/lara-asp/tree/main/packages/graphql#Printer

[//]: # (end: 6c180b37114202a8766bad1a59a3c0699948cc875617c85fd14a024e3bca64fe)
