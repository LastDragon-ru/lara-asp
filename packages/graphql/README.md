# GraphQL Extensions for Lighthouse

This package provides highly powerful `@searchBy` and `@sortBy`  directives for [lighthouse-php](https://lighthouse-php.com/). The `@searchBy` directive provides basic conditions like `=`, `>`, `<`, etc, relations, `not (<condition>)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The `@sortBy` is not only about standard sorting by columns but also allows use relations. 😎

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 7345502de8e33b9f2179e1d5e492a19bdc4b3d1012d77ee610aa6205dad3530b)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |  `HEAD`  ,  `5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.5.0`   |
|  Laravel  | `^10.0.0` |   `HEAD ⋯ 2.1.0`   |
|  | `^9.21.0` |   `HEAD ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.5.0`   |
|  Lighthouse  | `^6.5.0` |   `HEAD ⋯ 5.0.0-beta.0`   |
|  | `^6.0.0` |   `4.6.0 ⋯ 4.0.0`   |
|  | `^5.68.0` |   `3.0.0 ⋯ 2.0.0`   |
|  | `^5.8.0` |   `1.1.2 ⋯ 0.13.0`   |
|  | `^5.6.1` |  `0.12.0`  ,  `0.11.0`   |
|  | `^5.4` |   `0.10.0 ⋯ 0.5.0`   |

[//]: # (end: 7345502de8e33b9f2179e1d5e492a19bdc4b3d1012d77ee610aa6205dad3530b)

# Installation

```shell
composer require lastdragon-ru/lara-asp-graphql
```

# Configuration

Config can be used, for example, to customize supported operators for each type. Before this, you need to publish it via the following command, and then you can edit `config/lara-asp-graphql.php`.

```shell
php artisan vendor:publish --provider=LastDragon_ru\\LaraASP\\GraphQL\\Provider --tag=config
```

# Directives

[include:document-list]: ./docs/Directives
[//]: # (start: ac98e04e18d99ce0a6af07947adce086ad2450bda152abe31548ebe09831ec9a)
[//]: # (warning: Generated automatically. Do not edit.)

## `@searchBy`

Probably the most powerful directive to provide search (`where` conditions) for your GraphQL queries.

[Read more](<docs/Directives/@searchBy.md>).

## `@sortBy`

Probably the most powerful directive to provide sort (`order by` conditions) for your GraphQL queries.

[Read more](<docs/Directives/@sortBy.md>).

## `@stream` 🧪

Unlike the `@paginate` (and similar) directive, the `@stream` provides a uniform way to perform Offset/Limit and Cursor pagination of Eloquent/Query/Scout builders. Filtering and sorting enabled by default via [`@searchBy`][pkg:graphql#@searchBy] and [`@sortBy`][pkg:graphql#@sortBy] directives.

[Read more](<docs/Directives/@stream.md>).

[//]: # (end: ac98e04e18d99ce0a6af07947adce086ad2450bda152abe31548ebe09831ec9a)

# Scalars

> [!IMPORTANT]
>
> You should register the Scalar before use, it can be done via [`AstManipulator`](./src/Utils/AstManipulator.php) (useful while AST manipulation), [`TypeRegistry`](https://lighthouse-php.com/master/digging-deeper/adding-types-programmatically.html#using-the-typeregistry), or as a custom scalar inside the Schema:
>
> ```graphql
> scalar JsonString
> @scalar(
>     class: "LastDragon_ru\\LaraASP\\GraphQL\\Scalars\\JsonStringType"
> )
> ```

[include:document-list]: ./docs/Scalars
[//]: # (start: e3795f388ca164b6568d7e4b8d642c7a6ad049711bb0777e6b09e9b5b19e1e11)
[//]: # (warning: Generated automatically. Do not edit.)

## `JsonString`

Represents [JSON](https://json.org) string.

[Read more](<docs/Scalars/JsonString.md>).

[//]: # (end: e3795f388ca164b6568d7e4b8d642c7a6ad049711bb0777e6b09e9b5b19e1e11)

# Scout

[Scout](https://laravel.com/docs/scout) is also supported 🤩. By default `@searchBy`/`@sortBy` will convert nested/related properties into dot string: eg `{user: {name: asc}}` will be converted into `user.name`. You can redefine this behavior by [`FieldResolver`](./src/Builder/Contracts/Scout/FieldResolver.php):

```php
// AppProvider

$this->app->bind(
    LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver::class,
    MyScoutColumnResolver::class,
);
```

# Builder type detection

Directives like `@searchBy`/`@sortBy` have a unique set of operators and other features for each type of Builder (Eloquent/Scout/etc). Detection of the current Builder works fine for standard Lighthouse directives like `@all`, `@paginated`, `@search`, etc and relies on proper type hints of Relations/Queries/Resolvers. You may get `BuilderUnknown` error if the type hint is missed or the union type is used.

```php
<?php declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model {
    protected $table = 'comments';

    /**
     * Will NOT work
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Must be
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
```

If you implement custom directives which internally enhance the Builder (like standard directives do), you may get `BuilderUnknown` error because the proper/expected builder type was not detected. In this case, your directive should implement [`BuilderInfoProvider`](./src/Builder/Contracts/BuilderInfoProvider.php) interface and to specify the builder type explicitly.

```php
<?php declare(strict_types = 1);

namespace App\GraphQL\Directives;

use Illuminate\Database\Eloquent\Builder;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderInfoProvider;
use Nuwave\Lighthouse\Support\Contracts\Directive;

class CustomDirective implements Directive, BuilderInfoProvider {
    public static function definition(): string {
        return 'directive @custom';
    }

    public function getBuilderInfo(): BuilderInfo|string {
        return Builder::class;
    }

    public function __invoke(): mixed {
        // TODO: Implement __invoke() method.
    }
}
```

# Printer

The package provides bindings for [`Printer`][pkg:graphql-printer] so you can simply use:

[include:example]: ./docs/Examples/Printer.php
[//]: # (start: fe3f65d119ce56b1fa1557c42661c5d2da7093f9190bfc4c41bdf509ab34f755)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\DefaultSettings;
use Nuwave\Lighthouse\Schema\SchemaBuilder;

$schema   = Container::getInstance()->make(SchemaBuilder::class)->schema();
$printer  = Container::getInstance()->make(Printer::class);
$settings = new DefaultSettings();

$printer->setSettings(
    $settings->setDirectiveDefinitionFilter(
        new class() implements DirectiveFilter {
            public function isAllowedDirective(string $directive, bool $isStandard): bool {
                return !in_array($directive, ['eq', 'all', 'find'], true);
            }
        },
    ),
);

Example::raw($printer->print($schema), 'graphql');
```

<details><summary>Example output</summary>

The `$printer->print($schema)` is:

```graphql
"""
Use Input as Search Conditions for the current Builder.
"""
directive @searchBy
on
    | ARGUMENT_DEFINITION

directive @searchByOperatorAllOf
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorAnyOf
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorContains
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorEndsWith
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorEqual
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorIn
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorLike
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorNot
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorNotEqual
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorNotIn
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorNotLike
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorProperty
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

directive @searchByOperatorStartsWith
on
    | ENUM
    | INPUT_FIELD_DEFINITION
    | SCALAR

"""
Available conditions for `type User` (only one property allowed at a time).
"""
input SearchByConditionUser {
    """
    All of the conditions must be true.
    """
    allOf: [SearchByConditionUser!]
    @searchByOperatorAllOf

    """
    Any of the conditions must be true.
    """
    anyOf: [SearchByConditionUser!]
    @searchByOperatorAnyOf

    """
    Property condition.
    """
    id: SearchByScalarID
    @searchByOperatorProperty

    """
    Property condition.
    """
    name: SearchByScalarString
    @searchByOperatorProperty

    """
    Not.
    """
    not: SearchByConditionUser
    @searchByOperatorNot
}

"""
Available operators for `scalar ID` (only one operator allowed at a time).
"""
input SearchByScalarID {
    """
    Equal (`=`).
    """
    equal: ID
    @searchByOperatorEqual

    """
    Within a set of values.
    """
    in: [ID!]
    @searchByOperatorIn

    """
    Not Equal (`!=`).
    """
    notEqual: ID
    @searchByOperatorNotEqual

    """
    Outside a set of values.
    """
    notIn: [ID!]
    @searchByOperatorNotIn
}

"""
Available operators for `scalar String` (only one operator allowed at a time).
"""
input SearchByScalarString {
    """
    Contains.
    """
    contains: String
    @searchByOperatorContains

    """
    Ends with a string.
    """
    endsWith: String
    @searchByOperatorEndsWith

    """
    Equal (`=`).
    """
    equal: String
    @searchByOperatorEqual

    """
    Within a set of values.
    """
    in: [String!]
    @searchByOperatorIn

    """
    Like.
    """
    like: String
    @searchByOperatorLike

    """
    Not Equal (`!=`).
    """
    notEqual: String
    @searchByOperatorNotEqual

    """
    Outside a set of values.
    """
    notIn: [String!]
    @searchByOperatorNotIn

    """
    Not like.
    """
    notLike: String
    @searchByOperatorNotLike

    """
    Starts with a string.
    """
    startsWith: String
    @searchByOperatorStartsWith
}

type Query {
    """
    Find a single user by an identifying attribute.
    """
    user(
        """
        Search by primary key.
        """
        id: ID
        @eq
    ): User
    @find

    """
    List multiple users.
    """
    users(
        where: SearchByConditionUser
        @searchBy
    ): [User!]!
    @all
}

"""
Account of a person who utilizes this application.
"""
type User {
    """
    Unique primary key.
    """
    id: ID!

    """
    Non-unique name.
    """
    name: String!
}
```

</details>

[//]: # (end: fe3f65d119ce56b1fa1557c42661c5d2da7093f9190bfc4c41bdf509ab34f755)

There are also few great new [GraphQL Assertions](./src/Testing/GraphQLAssertions.php).

| Name                        | Description              |
|-----------------------------|--------------------------|
| `assertGraphQLSchemaEquals` | Compares default schema. |

[include:file]: ../../docs/shared/Contributing.md
[//]: # (start: 0001ad9d31b5a203286c531c6880292795cb49f2074223b60ae12c6faa6c42eb)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 0001ad9d31b5a203286c531c6880292795cb49f2074223b60ae12c6faa6c42eb)

[include:file]: ../../docs/shared/Links.md
[//]: # (start: d8baa2418c8dbf3ba09f9b223885c4326bee3e69a2dc0873e243f0d34e002a85)
[//]: # (warning: Generated automatically. Do not edit.)

[pkg:graphql#@searchBy]: https://github.com/LastDragon-ru/lara-asp/tree/main/packages/graphql/docs/Directives/@searchBy.md

[pkg:graphql#@sortBy]: https://github.com/LastDragon-ru/lara-asp/tree/main/packages/graphql/docs/Directives/@sortBy.md

[pkg:graphql-printer]: https://github.com/LastDragon-ru/lara-asp/tree/main/packages/graphql-printer

[//]: # (end: d8baa2418c8dbf3ba09f9b223885c4326bee3e69a2dc0873e243f0d34e002a85)
