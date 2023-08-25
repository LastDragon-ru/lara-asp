# The GraphQL Extensions

> This package is the part of Awesome Set of Packages for Laravel.
>
> [Read more](https://github.com/LastDragon-ru/lara-asp).

This package provides highly powerful `@searchBy` and `@sortBy`  directives for [lighthouse-php](https://lighthouse-php.com/). The `@searchBy` directive provides basic conditions like `=`, `>`, `<`, etc, relations, `not (<condition>)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The `@sortBy` is not only about standard sorting by columns but also allows use relations. 😎

[include:file]: ../../docs/shared/Requirements.md
[//]: # (start: 4aa299d1fd76a742656b8ab1b15d0ae7f7026ef1)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.0` |   `4.5.2 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^10.0.0` |   `HEAD ⋯ 2.1.0`   |
|  | `^9.21.0` |  `HEAD`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: 4aa299d1fd76a742656b8ab1b15d0ae7f7026ef1)

# Installation

```shell
composer require lastdragon-ru/lara-asp-graphql
```

# Configuration

Config can be used, for example, to customize supported operators for each type. Before this, you need to publish it via the following command, and then you can edit `config/lara-asp-graphql.php`.

```shell
php artisan vendor:publish --provider=LastDragon_ru\\LaraASP\\GraphQL\\Provider --tag=config
```

# `@searchBy` directive

At this moment this is probably the most powerful directive to provide search (`where` conditions) for your GraphQL queries.

## Basic usage

Out the box directives provides following features:

* Strictly typed - you can define supported operators for each Scalar;
* Eloquent Builder, Query Builder and Custom Builder support;
* Support almost all `where` operators;
* Enums support;
* `not (<condition>)` support;
* Relations support (Eloquent Builder only);
* Custom operators support
* easy to use and safe.

Let's start:

```graphql
scalar Date @scalar(class: "Nuwave\\Lighthouse\\Schema\\Types\\Scalars\\Date")

type Query {
    users(where: _ @searchBy): [User!]! @all
    comments(where: CommentsQuery @searchBy): [Comment!]! @all
}

input UsersQuery {
    id: ID!
    name: String!
}

input CommentsQuery {
    text: String!
    user: UsersQuery
    date: Date
}

type User {
    id: ID!
    name: String!
}

input Comment {
    text: String!
    user: User
    date: Date
}
```

That's all, just search 😃 (or look at [generated GraphQL schema](./src/SearchBy/Directives/DirectiveTest~example-expected.graphql))

```graphql
query {
    # WHERE name = "LastDragon"
    users(where: {
        name: {equal: "LastDragon"}
    }) {
        id
    }

    # WHERE name != "LastDragon"
    users(where: {
        name: {notEqual: "LastDragon"}
    }) {
        id
    }

    # WHERE name = "LastDragon" or name = "Aleksei"
    users(where: {
        anyOf: [
            {name: {equal: "LastDragon"}}
            {name: {equal: "Aleksei"}}
        ]
    }) {
        id
    }

    # WHERE NOT (name = "LastDragon" or name = "Aleksei")
    users(where: {
        not: {
            anyOf: [
                {name: {equal: "LastDragon"}}
                {name: {equal: "Aleksei"}}
            ]
        }
    }) {
        id
    }

    # WHERE date IS NULL
    users(where: {
        date: {isNull: yes}
    }) {
        id
    }

    # Relation: WHERE EXIST (related table)
    comments(where: {
        user: {
            where: {
                date: {between: {min: "2021-01-01", max: "2021-04-01"}}
            }
        }
    }) {
        id
    }

    # Relation: WHERE COUNT (related table) = 2
    comments(where: {
        user: {
            where: {
                date: {between: {min: "2021-01-01", max: "2021-04-01"}}
            }
            count: {
                equal: 2
            }
        }
    }) {
        id
    }
}
```

## Input type auto-generation

As you can see in the example above you can use the special placeholder `_` instead of real `input`. In this case, `@searchBy` will generate `input` automatically by the actual `type` of the query. While converting `type` into `input` following fields will be excluded:

* unions
* with `@field` directive
* with `@searchByIgnored` directive
* with any directive that implements [`Ignored`](./src/SearchBy/Contracts/Ignored.php)
* any `Type` that implements [`Ignored`](./src/SearchBy/Contracts/Ignored.php)

## Operators

There are three types of operators:

* Comparison - used to compare column with value(s), eg `{equal: "value"}`, `{lt: 2}`, etc. To add your own you just need to implement [`Operator`](./src/Builder/Contracts/Operator.php) and add it to type(s);
* Extra - used to add additional fields, by default package provides few Logical operators which allow you to do eg `anyOf([{equal: "a"}, {equal: "b"}])`. Adding your own is the same: implement [`Operator`](./src/Builder/Contracts/Operator.php) and add it to `Operators::Extra` type;
* Condition - used to create conditions for nested Input types and allow implement any logic eg `whereHas`, `whereDoesntHave`, etc. All the same, but these operators should be explicitly added to the fields/input types, by default the [`Relation`](./src/SearchBy/Operators/Complex/Relation.php) operator will be used:

    ```graphql
    type Query {
        users(where: UsersQuery @searchBy): ID! @all
        comments(where: CommentsQuery @searchBy): ID! @all
    }

    input UsersQuery {
        id: ID!
        name: String!
    }

    input CommentsQuery {
        text: String!
        user: UsersQuery @myComplexOperator
    }
    ```

## Type Operators

By default, the package provide list of predefined operators for build-in GraphQL and Lighthouse types. To extend/replace the built-in list, you can use config and/or add directives to type/scalar/enum inside the schema. Directives is the recommended way and have priority over the config.

The package also defines a few own types in addition to the standard GraphQL types:

* `SearchByCondition` / [`Operators::Condition`](./src/SearchBy/Operators.php) - List of known Condition operators. If no directive is found, the first supported operator from the list will be used.
* `SearchByNumber` / [`Operators::Number`](./src/SearchBy/Operators.php) - Any operator for this type will be available for `Int` and `Float`.
* `SearchByNull` / [`Operators::Null`](./src/SearchBy/Operators.php) - Additional operators available for nullable fields.
* `SearchByExtra` / [`Operators::Extra`](./src/SearchBy/Operators.php) - List of additional extra operators for all types.
* `SearchByEnum` / [`Operators::Enum`](./src/SearchBy/Operators.php) - Default operators for enums.

### GraphQL

```graphql
scalar MyScalar
@searchByOperators(type: "MyScalar")    # Re-use operators for `MyScalar` from config
@searchByOperators(type: "Int")         # Re-use operators from `Int` from schema
@searchByOperatorEqual                  # Package operator
@myOperator                             # Custom operator
```

### Schema

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBetweenDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;

/**
 * -----------------------------------------------------------------------------
 * GraphQL Settings
 * -----------------------------------------------------------------------------
 *
 * @var array{
 *      search_by: array{
 *          operators: array<string, list<string|class-string<Operator>>>
 *      }
 *      } $settings
 */
$settings = [
    'search_by' => [
        'operators' => [
            // You can define a list of operators for each type
            'Date'     => [
                SearchByOperatorEqualDirective::class,
                SearchByOperatorBetweenDirective::class,
                MyCustomOperator::class,
            ],

            // Or re-use existing type
            'DateTime' => [
                'Date',
            ],

            // Or re-use built-in type
            'Int' => [
                'Int',                  // built-in operators for `Int` will be used
                MyCustomOperator::class,
            ],

            // You can also use enum name to redefine default operators for it:
            'MyEnum' => [
                'Boolean',
            ],
        ],
    ],
];

return $settings;
```

# `@sortBy` directive

## Basic usage

How to use (and [generated GraphQL schema](./src/SortBy/Directives/DirectiveTest~example-expected.graphql)):

```graphql
type Query {
    "You can use normal input type"
    users(order: UsersSort @sortBy): ID! @all

    "or `_` to generate type automatically 😛"
    comments(order: _ @sortBy): [Comment!]! @all
}

input UsersSort {
    id: ID!
    name: String!
}

type Comment {
    text: String
    user: User
}

type User {
    id: ID!
    name: String!
}
```

And:

```graphql
query {
    # ORDER BY user.name ASC, text DESC
    comments(order: [
        {user: {name: asc}}
        {text: desc}
    ])
}
```

## Input type auto-generation

As you can see in the example above you can use the special placeholder `_` instead of real `input`. In this case, `@sortBy` will generate `input` automatically by the actual `type` of the query. While converting `type` into `input` following fields will be excluded:

* unions
* with list/array type
* with `@field` directive
* with `@sortByIgnored` directive
* with any directive that implements [`Ignored`](./src/SortBy/Contracts/Ignored.php)
* any `Type` that implements [`Ignored`](./src/SortBy/Contracts/Ignored.php)

## Operators

The package defines only one's own type. To extend/replace the list of its operators, you can use config and/or add directives to scalar/enum inside the schema. Directives is the recommended way and have priority over the config. Please see [`@searchBy`](#type-operators) for examples.

* `SortByExtra` / [`Operators::Extra`](./src/SortBy/Operators.php) - List of additional extra operators for all types. The list is empty by default.

## Eloquent/Database

### Supported Relations

The main feature - the ability to sort results by relation properties, at the moment supported the following relation types:

* `HasOne` (<https://laravel.com/docs/eloquent-relationships#one-to-one>)
* `HasMany` (<https://laravel.com/docs/eloquent-relationships#one-to-many>)
* `HasManyThrough` (<https://laravel.com/docs/eloquent-relationships#has-many-through>)
* `BelongsTo` (<https://laravel.com/docs/eloquent-relationships#one-to-many-inverse>)
* `BelongsToMany` (<https://laravel.com/docs/eloquent-relationships#many-to-many>)
* `MorphOne` (<https://laravel.com/docs/eloquent-relationships#one-of-many-polymorphic-relations>)
* `MorphMany` (<https://laravel.com/docs/eloquent-relationships#one-to-many-polymorphic-relations>)
* `MorphToMany` (<https://laravel.com/docs/eloquent-relationships#many-to-many-polymorphic-relations>)
* `HasOneThrough` (<https://laravel.com/docs/eloquent-relationships#has-one-through>)

### Order by random

It is also possible to sort records in random order, but it is not enabled by default. To enable it you just need to add [`Random`](./src/SortBy/Operators/Extra/Random.php)/`@sortByOperatorRandom` operator/directive to `Extra` type:

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators as SortByOperators;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorRandomDirective;

/**
 * -----------------------------------------------------------------------------
 * GraphQL Settings
 * -----------------------------------------------------------------------------
 *
 * @var array{
 *      sort_by: array{
 *          operators: array<string, list<string|class-string<Operator>>>
 *      },
 *      } $settings
 */
$settings = [
    'sort_by'   => [
        'operators' => [
            SortByOperators::Extra => [
                SortByOperatorRandomDirective::class,
            ],
        ],
    ],
];

return $settings;
```

or

```graphql
scalar SortByExtra
@sortByOperatorRandom
```

And after this, you can 🎉

```graphql
query {
    # ORDER BY RANDOM()
    comments(order: [
        {random: yes}
    ])
}
```

# Scout

[Scout](https://laravel.com/docs/scout) is also supported 🤩. By default `@searchBy`/`@sortBy` will convert nested/related properties into dot string: eg `{user: {name: asc}}` will be converted into `user.name`. You can redefine this behavior by [`FieldResolver`](./src/Builder/Contracts/Scout/FieldResolver.php):

```php
// AppProvider

$this->app->bind(
    LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver::class,
    MyScoutColumnResolver::class,
);
```

# Relations

Important note about relations: they must have proper type-hint, so:

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

# Builder type detection

Directives like `@searchBy`/`@sortBy` may have a unique set of operators for each type of Builder (Eloquent/Scout/etc). Detection of the current Builder works fine for standard Lighthouse directives like `@all`, `@paginated`, `@search`, etc in most cases (in others cases you probably just need to check/specify proper type hint the same as for relations). But if you implement custom directives which internally enhance the Builder (like standard directives do), you may get `BuilderUnknown` error because the proper/expected builder type was not detected. In this case, your directive should implement [`BuilderInfoProvider`](./src/Builder/Contracts/BuilderInfoProvider.php) interface and to specify the builder type explicitly.

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

The package provides bindings for [`Printer`](../graphql-printer/README.md) so you can simply use:

```php
<?php declare(strict_types = 1);

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\GraphQLPrinter\Printer;
use Nuwave\Lighthouse\Schema\SchemaBuilder;

$schema  = Container::getInstance()->make(SchemaBuilder::class)->schema();
$printer = Container::getInstance()->make(Printer::class);
$printed = $printer->print($schema);
```

There are also few great new [GraphQL Assertions](./src/Testing/GraphQLAssertions.php).

| Name                        | Description              |
|-----------------------------|--------------------------|
| `assertGraphQLSchemaEquals` | Compares default schema. |
