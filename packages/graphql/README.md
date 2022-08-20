# The GraphQL Extensions.

> This package is the part of Awesome Set of Packages for Laravel.
>
> [Read more](https://github.com/LastDragon-ru/lara-asp).

This package provides highly powerful `@searchBy` and `@sortBy`  directives for [lighthouse-php](https://lighthouse-php.com/). The `@searchBy` directive provides basic conditions like `=`, `>`, `<`, etc, relations, `not (<condition>)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The `@sortBy` is not only about standard sorting by columns but also allows use relations. 😎


# Installation

```shell
composer require lastdragon-ru/lara-asp-graphql
```


# `@searchBy` directive

At this moment this is probably the most powerful directive to provide search (`where` conditions) for your GraphQL queries.


## Basic usage

Out the box directives provides following features:

+ Strictly typed - you can define supported operators for each Scalar;
+ Eloquent Builder, Query Builder and Custom Builder support;
+ Support almost all `where` operators;
+ Enums support;
+ `not (<condition>)` support;
+ Relations support (Eloquent Builder only);
+ Custom operators support
+ easy to use and safe.

Let's start:

```graphql
scalar Date @scalar(class: "Nuwave\\Lighthouse\\Schema\\Types\\Scalars\\Date")

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
  user: UsersQuery
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


## Scalars

In addition to standard GraphQL scalars the package defines few own:

* `LastDragon_ru\\LaraASP\\GraphQL\\SearchBy\\Directives\\Directive::ScalarNumber` - any operator for this scalar will be available for `Int` and `Float`;
* `LastDragon_ru\\LaraASP\\GraphQL\\SearchBy\\Directives\\Directive::ScalarNull` - additional operators available for nullable scalars;
* `LastDragon_ru\\LaraASP\\GraphQL\\SearchBy\\Directives\\Directive::ScalarLogic` - list of logical operators, please see below;
* `LastDragon_ru\\LaraASP\\GraphQL\\SearchBy\\Directives\\Directive::ScalarEnum` - default operators for enums;

To work with custom scalars you need to configure supported operators for each of them. First, you need to publish package config:

```shell
php artisan vendor:publish --provider=LastDragon_ru\\LaraASP\\GraphQL\\Provider --tag=config
```

And then edit `config/lara-asp-graphql.php`

```php
<?php declare(strict_types = 1);

/**
 * -----------------------------------------------------------------------------
 * GraphQL Settings
 * -----------------------------------------------------------------------------
 */

use App\GraphQL\Operators\MyCustomOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Comparison\Equal;

return [
    /**
     * Settings for @searchBy directive.
     */
    'search_by' => [
        /**
         * Scalars
         * ---------------------------------------------------------------------
         *
         * You can (re)define scalars and supported operators here.
         *
         * @var array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator>>>
         */
        'scalars' => [
            // You can define a list of operators for each Scalar
            'Date'     => [
                Equal::class,
                Between::class,
                MyCustomOperator::class,
            ],

            // Or re-use existing type
            'DateTime' => 'Date',

            // You can also use enum name to redefine default operators for it:
            'MyEnum' => 'Boolean',
        ],
    ],
];
```


## Operators

There are three types of operators:

* Comparison - used to compare column with value(s), eg `{equal: "value"}`, `{lt: 2}`, etc. To add your own you just need to implement [`Operator`](./src/SearchBy/Contracts/Operator.php) and add it to scalar(s);
* Logical - used to group comparisons into groups, eg `anyOf([{equal: "a"}, {equal: "b"}])`. Adding your own is the same: implement [`Operator`](./src/SearchBy/Contracts/Operator.php) and add it to `Directive::ScalarLogic` scalar;
* Complex - used to create conditions for nested Input types and allow implement any logic eg `whereHas`, `whereDoesntHave`, etc. These operators must implement [`ComplexOperator`](./src/SearchBy/Contracts/ComplexOperator.php) (by default the [`Relation`](./src/SearchBy/Operators/Complex/Relation.php) operator will be used, you can use it as example):

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


# `@sortBy` directive

## Eloquent/Database

The main feature - the ability to sort results by relation properties, at the moment supported the following relation types:

- `HasOne` (https://laravel.com/docs/eloquent-relationships#one-to-one)
- `HasMany` (https://laravel.com/docs/eloquent-relationships#one-to-many)
- `HasManyThrough` (https://laravel.com/docs/eloquent-relationships#has-many-through)
- `BelongsTo` (https://laravel.com/docs/eloquent-relationships#one-to-many-inverse)
- `BelongsToMany` (https://laravel.com/docs/eloquent-relationships#many-to-many)
- `MorphOne` (https://laravel.com/docs/eloquent-relationships#one-of-many-polymorphic-relations)
- `MorphMany` (https://laravel.com/docs/eloquent-relationships#one-to-many-polymorphic-relations)
- `MorphToMany` (https://laravel.com/docs/eloquent-relationships#many-to-many-polymorphic-relations)
- `HasOneThrough` (https://laravel.com/docs/eloquent-relationships#has-one-through)


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


## Scout

[Scout](https://laravel.com/docs/scout) is also supported 🤩 (tested on v9). By default `@sortBy` will convert nested/related properties into dot string: `{user: {name: asc}}` will be converted into `user.name`. You can redefine this behavior by [`ScoutColumnResolver`](./src/SortBy/Contracts/ScoutColumnResolver.php):

```php
// AppProvider

$this->app->bind(
    LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout\ColumnResolver::class,
    MyScoutColumnResolver::class,
);
```


## Input type auto-generation

As you can see in the example above you can use the special placeholder `_` instead of real `input`. In this case, `@sortBy` will generate `input` automatically by the actual `type` of the query. While converting `type` into `input` following fields will be excluded:

- with list/array type
- with `@field` directive
- with `@sortByIgnored` directive
- with any directive that implements [`Ignored`](./src/SortBy/Contracts/Ignored.php)


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
