# `@sortBy`

Probably the most powerful directive to provide sort (`order by` conditions) for your GraphQL queries.

[include:exec]: <../../../../dev/artisan dev:directive @sortBy>
[//]: # (start: 43598e15e6936de056c8a256e3edb09c54520b27272d7b94115993712e8bec0b)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Use Input as Sort Conditions for the current Builder.
"""
directive @sortBy
on
    | ARGUMENT_DEFINITION
```

[//]: # (end: 43598e15e6936de056c8a256e3edb09c54520b27272d7b94115993712e8bec0b)

## Basic usage

How to use (and [generated GraphQL schema](../../src/SortBy/Directives/DirectiveTest~example-expected.graphql)):

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
* with any directive that implements [`Ignored`](../../src/SortBy/Contracts/Ignored.php)
* any `Type` that implements [`Ignored`](../../src/SortBy/Contracts/Ignored.php)

## Operators

The package defines only one's own type. To extend/replace the list of its operators, you can use config and/or add directives to scalar/enum inside the schema. Directives is the recommended way and have priority over the config. Please see [`@searchBy`](@searchBy.md#type-operators) for examples.

* `SortByExtra` / [`Operators::Extra`](../../src/SortBy/Operators.php) - List of additional extra operators for all types. The list is empty by default.

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

It is also possible to sort records in random order, but it is not enabled by default. To enable it you just need to add [`Random`](../../src/SortBy/Operators/Extra/Random.php)/`@sortByOperatorRandom` operator/directive to `Extra` type:

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

### NULLs ordering

`NULL`s order different in different databases. Sometimes you may to change it. There is no default/built-it support in Laravel nor Lighthouse, but you can do it! :) Please note, not all databases have native `NULLS FIRST`/`NULLS LAST` support (eg MySQL and SQL Server doesn't). The additional `ORDER BY` clause with `CASE WHEN` will be used for these databases. It may be slow for big datasets.

Default ordering can be changed via config. You may set it for all directions if single value used, in this case NULL always be first/last:

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;

/**
 * @var array{
 *      sort_by: array{
 *          nulls: Nulls|non-empty-array<value-of<Direction>, Nulls>|null,
 *      },
 *      } $settings
 */
$settings = [
    'sort_by' => [
        'nulls' => Nulls::First,
    ],
];

return $settings;
```

Or individually for each direction:

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;

/**
 * @var array{
 *      sort_by: array{
 *          nulls: Nulls|non-empty-array<value-of<Direction>, Nulls>|null,
 *      },
 *      } $settings
 */
$settings = [
    'sort_by' => [
        'nulls' => [
            Direction::Asc->value  => Nulls::First,
            Direction::Desc->value => Nulls::Last,
        ],
    ],
];

return $settings;
```
