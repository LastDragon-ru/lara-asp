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
+ Custom operators support
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
Conditions for the related objects (`has()`/`doesntHave()`) for input UsersQuery.

See also:
* https://laravel.com/docs/8.x/eloquent-relationships#querying-relationship-existence
* https://laravel.com/docs/8.x/eloquent-relationships#querying-relationship-absence
"""
input SearchByComplexRelationUsersQuery {
  """Additional conditions."""
  where: SearchByConditionUsersQuery

  """Count conditions."""
  count: SearchByScalarInt

  """
  Shortcut for `doesntHave()`, same as:
  
  \```
  count: {
    lt: 1
  }
  \```
  """
  not: Boolean! = false

  """Complex operator marker."""
  relation: SearchByTypeFlag! = yes
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
  not: SearchByConditionCommentsQuery

  """Property condition."""
  text: SearchByScalarString

  """Property condition."""
  user: SearchByComplexRelationUsersQuery

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
  not: SearchByConditionUsersQuery

  """Property condition."""
  id: SearchByScalarID

  """Property condition."""
  name: SearchByScalarString
}

"""
Available operators for scalar Date (only one operator allowed at a time).
"""
input SearchByScalarDateOrNull {
  """Equal (`=`)."""
  equal: Date

  """Not Equal (`!=`)."""
  notEqual: Date

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

  """Outside a set of values."""
  notIn: [Date!]

  """Within a range."""
  between: SearchByTypeRangeDate

  """Outside a range."""
  notBetween: SearchByTypeRangeDate

  """Is NULL?"""
  isNull: SearchByTypeFlag

  """Is NOT NULL?"""
  isNotNull: SearchByTypeFlag
}

"""
Available operators for scalar ID! (only one operator allowed at a time).
"""
input SearchByScalarID {
  """Equal (`=`)."""
  equal: ID

  """Not Equal (`!=`)."""
  notEqual: ID

  """Within a set of values."""
  in: [ID!]

  """Outside a set of values."""
  notIn: [ID!]
}

"""
Available operators for scalar Int! (only one operator allowed at a time).
"""
input SearchByScalarInt {
  """Equal (`=`)."""
  equal: Int

  """Not Equal (`!=`)."""
  notEqual: Int

  """Less than (`<`)."""
  lt: Int

  """Less than or equal to (`<=`)."""
  lte: Int

  """Greater than (`>`)."""
  gt: Int

  """Greater than or equal to (`>=`)."""
  gte: Int

  """Within a set of values."""
  in: [Int!]

  """Outside a set of values."""
  notIn: [Int!]

  """Within a range."""
  between: SearchByTypeRangeInt

  """Outside a range."""
  notBetween: SearchByTypeRangeInt
}

"""
Available operators for scalar String! (only one operator allowed at a time).
"""
input SearchByScalarString {
  """Equal (`=`)."""
  equal: String

  """Not Equal (`!=`)."""
  notEqual: String

  """Like."""
  like: String

  """Not like."""
  notLike: String

  """Within a set of values."""
  in: [String!]

  """Outside a set of values."""
  notIn: [String!]
}

enum SearchByTypeFlag {
  yes
}

input SearchByTypeRangeDate {
  min: Date!
  max: Date!
}

input SearchByTypeRangeInt {
  min: Int!
  max: Int!
}

input UsersQuery {
  id: ID!
  name: String!
}

```

</details>


## Scalars

In addition to standard GraphQL scalars package defines few own:

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

* Comparison - used to compare column with value(s), eg `{equal: "value"}`, `{lt: 2}`, etc. To add your own you just need to implement [`ComparisonOperator`](./src/SearchBy/Contracts/ComparisonOperator.php) and add it to scalar(s);
* Logical - used to group comparisons into groups, eg `anyOf([{equal: "a"}, {equal: "b"}])`. Adding your own is the same: implement [`LogicalOperator`](./src/SearchBy/Contracts/LogicalOperator.php) and add it to `Directive::ScalarLogic` scalar;
* Complex - used to created conditions for nested Input types and allow implement any logic eg `whereHas`, `whereDoesntHave`, etc. These operators must implement [`ComplexOperator`](./src/SearchBy/Contracts/ComplexOperator.php) and then should be added for nested input with `@searchByOperator` (by default will be used [`Relation`](./src/SearchBy/Operators/Complex/Relation.php) operator):
  
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
      user: UsersQuery @searchByOperator(class: "App\\MyComplexOperator")
    }
    ```


# `@sortBy` directive

## Eloquent/Database

The main feature - the ability to sort results by relation properties, at the moment supported the following relation types:

- `HasOne` (https://laravel.com/docs/8.x/eloquent-relationships#one-to-one)
- `BelongsTo` (https://laravel.com/docs/8.x/eloquent-relationships#one-to-many-inverse)
- `MorphOne` (https://laravel.com/docs/8.x/eloquent-relationships#one-to-one-polymorphic-relations)
- `HasOneThrough` (https://laravel.com/docs/8.x/eloquent-relationships#has-one-through)


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


## Scout

[Scout](https://laravel.com/docs/8.x/scout) is also supported 🤩 (tested on v9). By default `@sortBy` will convert nested/related properties into dot string: `{user: {name: asc}}` will be converted into `user.name`. You can redefine this behavior by [`ScoutColumnResolver`](./src/SortBy/Contracts/ScoutColumnResolver.php):

```php
// AppProvider

$this->app->bind(
    LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\ScoutColumnResolver::class,  
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
