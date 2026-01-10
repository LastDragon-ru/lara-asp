# `@sortBy`

Probably the most powerful directive to provide sort (`order by` conditions) for your GraphQL queries.

[include:graphql-directive]: @sortBy
[//]: # (start: preprocess/a535f5f615253788)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Use Input as Sort Conditions for the current Builder.
"""
directive @sortBy
on
    | ARGUMENT_DEFINITION
```

[//]: # (end: preprocess/a535f5f615253788)

## Basic usage

How to use (and [generated GraphQL schema](../../src/SortBy/Directives/DirectiveTest/Example.expected.graphql)):

[include:example]: ../../src/SortBy/Directives/DirectiveTest/Example.schema.graphql
[//]: # (start: preprocess/c5e6df7fefc39ee8)
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

[//]: # (end: preprocess/c5e6df7fefc39ee8)

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
[//]: # (start: preprocess/2d63dba3a5c8ec51)
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

[//]: # (end: preprocess/2d63dba3a5c8ec51)

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

[include:example]: @sortByConfigOrderByRandom.php
[//]: # (start: preprocess/d2d497ec780cf493)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorRandomDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

$config = PackageConfig::getDefaultConfig();

$config->sortBy->operators[Operators::Extra] = [
    SortByOperatorRandomDirective::class,
];

return $config;
```

[//]: # (end: preprocess/d2d497ec780cf493)

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

[include:example]: @sortByConfigNullsSingleValue.php
[//]: # (start: preprocess/0a3d52d172342702)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;

$config = PackageConfig::getDefaultConfig();

$config->sortBy->nulls = Nulls::First;

return $config;
```

[//]: # (end: preprocess/0a3d52d172342702)

Or individually for each direction:

[include:example]: @sortByConfigNullsArrayValue.php
[//]: # (start: preprocess/d7692bfa2035b990)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;

$config = PackageConfig::getDefaultConfig();

$config->sortBy->nulls = [
    Direction::Asc->value  => Nulls::First,
    Direction::Desc->value => Nulls::Last,
];

return $config;
```

[//]: # (end: preprocess/d7692bfa2035b990)

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
