# The GraphQL Printer

> This package is the part of Awesome Set of Packages for Laravel.
>
> [Read more](https://github.com/LastDragon-ru/lara-asp).

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema and Queries in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema/Query.

# Installation

```shell
composer require lastdragon-ru/lara-asp-graphql-printer
```

## Usage

```php
<?php declare(strict_types = 1);

use GraphQL\Utils\BuildSchema;
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
$printed  = $printer->print($type);
$exported = $printer->export($type);

echo $printed.PHP_EOL;
echo $exported.PHP_EOL;
```

The `print()` will print the current type only:

```graphql
type A
@a
{
    b: [B!]
    id: ID!
}
```

The `export()` will print current type and all used types/directives:

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

## Customization

Please see:

* [`Settings`](./src/Settings) directory to see built-in settings;
* [`Settings`](./src/Contracts/Settings.php) interface to see all supported settings;
* [`DirectiveResolver`](./src/Contracts/DirectiveResolver.php) interface to define your own way to find all available directives and their definitions;

## Filtering

The Printer allows filter out types and directives. This may be useful to exclude them from the schema completely. Filtering also works for queries. Please note that types filtering will work only if the schema is known (the schema is required to determine the type of argument nodes). For some AST node types, their type may also be required.

```php
<?php declare(strict_types = 1);

use GraphQL\Language\Parser;
use GraphQL\Utils\BuildSchema;
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

$settings      = (new DefaultSettings())
    ->setDirectiveFilter($directiveFilter)
    ->setTypeFilter($typeFilter);
$printer       = new Printer($settings, null, $schema);
$printedSchema = $printer->print($schema);
$printedQuery  = $printer->print($query);

echo $printedSchema.PHP_EOL;
echo $printedQuery;
```

The `$printedSchema`:

```graphql
directive @allowed
on
    | FIELD_DEFINITION

type Query {
    allowed: Boolean
    @allowed
}
```

The `$printedQuery`:

```graphql
query {
    allowed
}
```

## Laravel/Lighthouse

It is highly recommended to use [`lara-asp-graphql`](../graphql/README.md#Printer) package to use the `Printer` within the Laravel/Lighthouse application.

## Testing Assertions

Package also provides few great [GraphQL Assertions](./src/Testing/GraphQLAssertions.php):

| Name                            | Description                                               |
|---------------------------------|-----------------------------------------------------------|
| `assertGraphQLPrintableEquals`  | Prints and compares two GraphQL schemas/types/nodes/etc.  |
| `assertGraphQLExportableEquals` | Exports and compares two GraphQL schemas/types/nodes/etc. |
