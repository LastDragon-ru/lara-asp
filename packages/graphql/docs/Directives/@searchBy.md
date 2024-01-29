# `@searchBy`

Probably the most powerful directive to provide search (`where` conditions) for your GraphQL queries.

[include:exec]: <../../../../dev/artisan dev:directive @searchBy>
[//]: # (start: 69bf42cd0808d9d802898c40232dceb47e32be7a3a3d7ffca61cbdd6aa8a3e5b)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Use Input as Search Conditions for the current Builder.
"""
directive @searchBy
on
    | ARGUMENT_DEFINITION
```

[//]: # (end: 69bf42cd0808d9d802898c40232dceb47e32be7a3a3d7ffca61cbdd6aa8a3e5b)

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

[include:example]: ../../src/SearchBy/Directives/DirectiveTest/Example.schema.graphql
[//]: # (start: e552ccbddb2cf6a9dd4e14f9295ad974ca19c375ba683681d959d5190028ded4)
[//]: # (warning: Generated automatically. Do not edit.)

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
    user: User @belongsTo
    date: Date
}
```

[//]: # (end: e552ccbddb2cf6a9dd4e14f9295ad974ca19c375ba683681d959d5190028ded4)

That's all, just search 😃 (or look at [generated GraphQL schema](../../src/SearchBy/Directives/DirectiveTest/Example.expected.graphql))

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

As you can see in the example above you can use the special placeholder `_` instead of real `input`. In this case, `@searchBy` will generate `input` automatically by the actual `type` of the query. Please check the main section of [Input type auto-generation](../../README.md#input-type-auto-generation) to learn more about general conversion rules.

The `@searchByIgnored` can be used as Ignored marker.

[include:exec]: <../../../../dev/artisan dev:directive @searchByIgnored>
[//]: # (start: 20d300e04ef04c52684a5d3db6a419825ada6f67a950a418e26dee5c9b5d218c)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Marks that field/definition should be excluded from search.
"""
directive @searchByIgnored
on
    | ENUM
    | FIELD_DEFINITION
    | INPUT_FIELD_DEFINITION
    | INPUT_OBJECT
    | OBJECT
    | SCALAR
```

[//]: # (end: 20d300e04ef04c52684a5d3db6a419825ada6f67a950a418e26dee5c9b5d218c)

## Operators

There are three types of operators:

* Comparison - used to compare column with value(s), eg `{equal: "value"}`, `{lt: 2}`, etc. To add your own you just need to implement [`Operator`](../../src/Builder/Contracts/Operator.php) and add it to type(s);
* Extra - used to add additional fields, by default package provides few Logical operators which allow you to do eg `anyOf([{equal: "a"}, {equal: "b"}])`. Adding your own is the same: implement [`Operator`](../../src/Builder/Contracts/Operator.php) and add it to `Operators::Extra` type;
* Condition - used to create conditions for nested Input types and allow implement any logic eg `whereHas`, `whereDoesntHave`, etc. All the same, but these operators should be explicitly added to the fields/input types, by default the [`Relation`](../../src/SearchBy/Operators/Complex/Relation.php) operator will be used:

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

* `SearchByCondition` / [`Operators::Condition`](../../src/SearchBy/Operators.php) - List of known Condition operators. If no directive is found, the first supported operator from the list will be used.
* `SearchByNumber` / [`Operators::Number`](../../src/SearchBy/Operators.php) - Any operator for this type will be available for `Int` and `Float`.
* `SearchByNull` / [`Operators::Null`](../../src/SearchBy/Operators.php) - Additional operators available for nullable fields.
* `SearchByExtra` / [`Operators::Extra`](../../src/SearchBy/Operators.php) - List of additional extra operators for all types.
* `SearchByEnum` / [`Operators::Enum`](../../src/SearchBy/Operators.php) - Default operators for enums.

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
