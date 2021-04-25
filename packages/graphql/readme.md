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

Features:

+ Strictly typed - you can define supported operators for each Scalar;
+ Support both Query and Eloquent Builder
+ Support almost all `where` operators;
+ Enums support;
+ `not (<condition>)` support;
+ Relations support (except Query Builder);
+ Localization support;
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

That's all, just search 😃

```graphql
# Write your query or mutation here
query {
  # WHERE name = "LastDragon"
  users(where: {
    name: {eq: "LastDragon"}
  }) {
    id
  }

  # WHERE name != "LastDragon"
  users(where: {
    name: {eq: "LastDragon", not: yes}
  }) {
    id
  }

  # WHERE name = "LastDragon" or name = "Aleksei"
  users(where: {
    anyOf: [
      {name: {eq: "LastDragon"}}
      {name: {eq: "Aleksei"}}
    ]
  }) {
    id
  }

  # WHERE NOT (name = "LastDragon" or name = "Aleksei")
  users(where: {
    anyOf: [
      {name: {eq: "LastDragon"}}
      {name: {eq: "Aleksei"}}
    ]
    not: yes
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
      eq: 2
    }
  }) {
    id
  }
}
```

<details>
<summary>Generated GraphQL schema</summary>

```graphql
input CommentsQuery {
  text: String!
  user: UsersQuery
  date: Date
}

scalar Date

type Query {
  users(where: SearchByConditionUsersQuery): ID!
  comments(where: SearchByConditionCommentsQuery): ID!
}

"""
Available conditions for input CommentsQuery (only one property allowed at a time).
"""
input SearchByConditionCommentsQuery {
  """All of the conditions must be true."""
  allOf: [SearchByConditionCommentsQuery!]

  """Any of the conditions must be true."""
  anyOf: [SearchByConditionCommentsQuery!]

  """Not."""
  not: SearchByFlag

  """Property condition."""
  text: SearchByScalarString

  """Property condition."""
  user: SearchByRelationUsersQuery

  """Property condition."""
  date: SearchByScalarDateOrNull
}

"""
Available conditions for input UsersQuery (only one property allowed at a time).
"""
input SearchByConditionUsersQuery {
  """All of the conditions must be true."""
  allOf: [SearchByConditionUsersQuery!]

  """Any of the conditions must be true."""
  anyOf: [SearchByConditionUsersQuery!]

  """Not."""
  not: SearchByFlag

  """Property condition."""
  id: SearchByScalarID

  """Property condition."""
  name: SearchByScalarString
}

"""Flag."""
enum SearchByFlag {
  yes
}

"""Relation condition for input UsersQuery."""
input SearchByRelationUsersQuery {
  """Conditions for the related objects."""
  where: SearchByConditionUsersQuery!

  """Equal (`=`)."""
  eq: Int

  """Less than (`<`)."""
  lt: Int

  """Less than or equal to (`<=`)."""
  lte: Int

  """Greater than (`>`)."""
  gt: Int

  """Greater than or equal to (`>=`)."""
  gte: Int

  """Not."""
  not: SearchByFlag
}

input SearchByScalarDateOperatorBetween {
  min: Date!
  max: Date!
}

"""
Available operators for scalar Date (only one operator allowed at a time).
"""
input SearchByScalarDateOrNull {
  """Equal (`=`)."""
  eq: Date

  """Less than (`<`)."""
  lt: Date

  """Less than or equal to (`<=`)."""
  lte: Date

  """Greater than (`>`)."""
  gt: Date

  """Greater than or equal to (`>=`)."""
  gte: Date

  """Within a set of values."""
  in: [Date!]

  """Within a range."""
  between: SearchByScalarDateOperatorBetween

  """Is NULL?"""
  isNull: SearchByFlag

  """Not."""
  not: SearchByFlag
}

"""
Available operators for scalar ID! (only one operator allowed at a time).
"""
input SearchByScalarID {
  """Equal (`=`)."""
  eq: ID

  """Within a set of values."""
  in: [ID!]

  """Not."""
  not: SearchByFlag
}

"""
Available operators for scalar String! (only one operator allowed at a time).
"""
input SearchByScalarString {
  """Equal (`=`)."""
  eq: String

  """Like."""
  like: String

  """Within a set of values."""
  in: [String!]

  """Not."""
  not: SearchByFlag
}

input UsersQuery {
  id: ID!
  name: String!
}
```

</details>


## Custom scalars

First you need to publish package config:

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
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Equal;

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
        ],
    ],
];

```


# `@sortBy` directive

The main feature - the ability to sort results by relation properties, at the moment supported the following relation types:

- `HasOne` (https://laravel.com/docs/8.x/eloquent-relationships#one-to-one)
- `BelongsTo` (https://laravel.com/docs/8.x/eloquent-relationships#one-to-many-inverse)
- `MorphOne` (https://laravel.com/docs/8.x/eloquent-relationships#one-to-one-polymorphic-relations)


How to use:

```graphql
type Query {
  users(order: UsersSort @sortBy): ID! @all
  comments(order: CommentsSort @sortBy): ID! @all
}

input UsersSort {
  id: ID!
  name: String!
}

input CommentsSort {
  text: String
  user: UsersSort
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

<details>
<summary>Generated GraphQL schema</summary>

```graphql
input CommentsSort {
  text: String
  user: UsersSort
}

type Query {
  users(order: [SortByClauseUsersSort!]): ID!
  comments(order: [SortByClauseCommentsSort!]): ID!
}

"""
Sort clause for input CommentsSort (only one property allowed at a time).
"""
input SortByClauseCommentsSort {
  """Property clause."""
  text: SortByDirection

  """Property clause."""
  user: SortByClauseUsersSort
}

"""
Sort clause for input UsersSort (only one property allowed at a time).
"""
input SortByClauseUsersSort {
  """Property clause."""
  id: SortByDirection

  """Property clause."""
  name: SortByDirection
}

"""Sort direction."""
enum SortByDirection {
  asc
  desc
}

input UsersSort {
  id: ID!
  name: String!
}

```
</details>


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


# Localization

Some of the error messages can be translated, to do it you should publish translations and translate them

```shell
php artisan vendor:publish --provider=LastDragon_ru\\LaraASP\\GraphQL\\Provider --tag=translations
```


# Enums

Package also provides the helper to register [`Enum`](https://github.com/LastDragon-ru/lara-asp/blob/master/packages/core/src/Enum.php) as GraphQL Enum:

```php
<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL;

use App\MyEnum;
use Illuminate\Support\ServiceProvider;
use LastDragon_ru\LaraASP\GraphQL\Helpers\EnumHelper;
use Nuwave\Lighthouse\Schema\TypeRegistry;

class Provider extends ServiceProvider {
    public function register(): void {
        $registry = $this->app->make(TypeRegistry::class);

        $registry->register(EnumHelper::getType(MyEnum::class));
    }
}

```
