# The GraphQL Printer

> This package is the part of Awesome Set of Packages for Laravel.
>
> [Read more](https://github.com/LastDragon-ru/lara-asp).

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema.

## Usage

```php
<?php declare(strict_types = 1);

use GraphQL\Utils\BuildSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;

$schema   = BuildSchema::build('...');
$settings = new DefaultSettings();
$printer  = new Printer($settings);
$printed  = $printer->printSchema();
```

## Customization

Please see:

* [`Settings`](./src/Settings) directory to see built-in settings;
* [`Settings`](./src/Contracts/Settings.php) interface to see all supported settings;
* [`DirectiveResolver`](./src/Contracts/DirectiveResolver.php) interface to define your own way to find all available directives and their definitions;

## Laravel/Lighthouse

It is highly recommended to use [`graphql`](../graphql/README.md#Printer) package to use the `Printer` within the Laravel/Lighthouse application.

## Testing Assertions

Package also provides few great [GraphQL Assertions](./src/Testing/GraphQLAssertions.php):

| Name                            | Description           |
|---------------------------------|-----------------------|
| `assertGraphQLSchemaTypeEquals` | Compares schema type. |
| `assertGraphQLSchemaEquals`     | Compares any schemas. |
| `assertGraphQLTypeEquals`       | Compares any types.   |
