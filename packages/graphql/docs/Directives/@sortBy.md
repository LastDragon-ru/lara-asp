# `@sortBy`

Probably the most powerful directive to provide sort (`order by` conditions) for your GraphQL queries.

[include:graphql-directive]: @sortBy
[//]: # (start: e2d33c9307e49d5f53ac135415329a0b01b87bd56ea6f0dc6ca4ac9143395ffa)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Use Input as Sort Conditions for the current Builder.
"""
directive @sortBy
on
    | ARGUMENT_DEFINITION
```

[//]: # (end: e2d33c9307e49d5f53ac135415329a0b01b87bd56ea6f0dc6ca4ac9143395ffa)

## Basic usage

How to use (and [generated GraphQL schema](../../src/SortBy/Directives/DirectiveTest/Example.expected.graphql)):

[include:example]: ../../src/SortBy/Directives/DirectiveTest/Example.schema.graphql
[//]: # (start: f4e6a88c853cd9b15aa5c3388c1b61148589eddbc18b410b0e846695c5765e4f)
[//]: # (warning: Generated automatically. Do not edit.)

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
    user: User @belongsTo
}

type User {
    id: ID!
    name: String!
}
```

[//]: # (end: f4e6a88c853cd9b15aa5c3388c1b61148589eddbc18b410b0e846695c5765e4f)

And:

```graphql
query {
    # ORDER BY user.name ASC, text DESC
    comments(order: [
        {field: {user: {name: asc}}}
        {field: {text: desc}}
    ])
}
```

## Input type auto-generation

As you can see in the example above you can use the special placeholder `_` instead of real `input`. In this case, `@sortBy` will generate `input` automatically by the actual `type` of the query. Please check the main section of [Input type auto-generation](../../README.md#input-type-auto-generation) to learn more about general conversion rules.

Addition rules for Implicit type:

* The field is a list of `scalar`/`enum`? - exclude

The `@sortByIgnored` can be used as Ignored marker.

[include:graphql-directive]: @sortByIgnored
[//]: # (start: a49887f25c9b44ee37b7df8d7fe960d16fcb8a2ddc8681ffad8100c6b4a7462e)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Marks that field/definition should be excluded.
"""
directive @sortByIgnored
on
    | ENUM
    | FIELD_DEFINITION
    | INPUT_FIELD_DEFINITION
    | INPUT_OBJECT
    | OBJECT
    | SCALAR
```

[//]: # (end: a49887f25c9b44ee37b7df8d7fe960d16fcb8a2ddc8681ffad8100c6b4a7462e)

## Operators

The package defines only one's own type. To extend/replace the list of its operators, you can use config and/or add directives to scalar/enum inside the schema. Directives is the recommended way and have priority over the config. Please see [`@searchBy`](@searchBy.md#type-operators) for examples.

* `SortByOperatorsExtra` / [`Operators::Extra`](../../src/SortBy/Operators.php) - List of additional extra operators for all types. The list is empty by default.
* `SortByOperatorsDisabled` / [`Operators::Disabled`](../../src/SortBy/Operators.php) - Disabled operators.

## Eloquent/Database

### Order by random

It is also possible to sort records in random order, but it is not enabled by default. To enable it you just need to add [`Random`](../../src/SortBy/Operators/Extra/Random.php)/`@sortByOperatorRandom` operator/directive to `Extra` type:

```graphql
extend scalar SortByOperatorsExtra
@sortByExtendOperators
@sortByOperatorRandom
```

or via config

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

`NULL`s order different in different databases. Sometimes you may want to change it. There is no default/built-it support in Laravel nor Lighthouse, but you can do it! :) Please note, not all databases have native `NULLS FIRST`/`NULLS LAST` support (eg MySQL and SQL Server doesn't). The additional `ORDER BY` clause with `CASE WHEN` will be used for these databases. It may be slow for big datasets.

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

The query is also supported and have highest priority (will override default settings):

```graphql
query {
    # ORDER BY user.name ASC NULLS FIRST, text DESC
    comments(order: [
        {nullsFirst: {user: {name: asc}}}
        {field: {text: desc}}
    ])
}
```
