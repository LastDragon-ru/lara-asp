# `@searchBy`

Probably the most powerful directive to provide search (`where` conditions) for your GraphQL queries.

[include:graphql-directive]: @searchBy
[//]: # (start: 6fc59903c0b30f293d154a4e848626ee52d76fad258360243058c413339a0ba4)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Use Input as Search Conditions for the current Builder.
"""
directive @searchBy
on
    | ARGUMENT_DEFINITION
```

[//]: # (end: 6fc59903c0b30f293d154a4e848626ee52d76fad258360243058c413339a0ba4)

## Basic usage

Out of the box directives provides following features:

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
[//]: # (start: cc0325309f64ed94ef1e996dc2a624162a7ebb35fb0e02191bf74e20a17cb2f3)
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

type Comment {
    text: String!
    user: User @belongsTo
    date: Date
}
```

[//]: # (end: cc0325309f64ed94ef1e996dc2a624162a7ebb35fb0e02191bf74e20a17cb2f3)

That's all, just search 😃 (or look at [generated GraphQL schema](../../src/SearchBy/Directives/DirectiveTest/Example.expected.graphql))

```graphql
query {
    # WHERE name = "LastDragon"
    users(where: {
        field: { name: { equal: "LastDragon" } }
    }) {
        id
    }

    # WHERE name != "LastDragon"
    users(where: {
        field: { name: { notEqual: "LastDragon" } }
    }) {
        id
    }

    # WHERE name = "LastDragon" or name = "Aleksei"
    users(where: {
        anyOf: [
            { field: { name: { equal: "LastDragon" } } }
            { field: { name: { equal: "Aleksei" } } }
        ]
    }) {
        id
    }

    # WHERE NOT (name = "LastDragon" or name = "Aleksei")
    users(where: {
        not: {
            anyOf: [
                { field: { name: { equal: "LastDragon" } } }
                { field: { name: { equal: "Aleksei" } } }
            ]
        }
    }) {
        id
    }

    # WHERE date IS NULL
    users(where: {
        field: { date: { isNull: Yes } }
    }) {
        id
    }

    # Relationship: WHERE EXIST (related table)
    comments(where: {
        field: {
            user: {
                where: {
                    field: {
                        date: { between: { min: "2021-01-01", max: "2021-04-01" } }
                    }
                }
            }
        }
    }) {
        id
    }

    # Relationship: WHERE COUNT (related table) = 2
    comments(where: {
        field: {
            user: {
                where: {
                    field: {
                        date: { between: { min: "2021-01-01", max: "2021-04-01"} }
                    }
                }
                count: {
                    equal: 2
                }
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

[include:graphql-directive]: @searchByIgnored
[//]: # (start: a9b2c25f23dff4186502cc93cc34a25e406edce92710315aecdfae2bbe0d1d1a)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Marks that field/definition should be excluded.
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

[//]: # (end: a9b2c25f23dff4186502cc93cc34a25e406edce92710315aecdfae2bbe0d1d1a)

## Operators

There are three types of operators:

* Comparison - used to compare column with value(s), eg `{equal: "value"}`, `{lt: 2}`, etc. To add your own you just need to implement [`Operator`](../../src/Builder/Contracts/Operator.php) and add it to type(s);
* Extra - used to add additional fields, by default package provides few Logical operators which allow you to do eg `anyOf([{equal: "a"}, {equal: "b"}])`. Adding your own is the same: implement [`Operator`](../../src/Builder/Contracts/Operator.php) and add it to `Operators::Extra` type;
* Object - used to create conditions for fields with type `Object` (`input`/`type`/`interface`) and allow implement any logic eg `whereHas`, `whereDoesntHave`, etc. All the same, but these operators should be explicitly added to the fields/input types, by default the [`Relationship`](../../src/SearchBy/Operators/Complex/Relationship.php) operator will be used:

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

* `SearchByOperatorsObject` / [`Operators::Object`](../../src/SearchBy/Operators.php) - List of known operators for `Object`. If no other directive is found, the first supported operator from the list will be used.
* `SearchByOperatorsNumber` / [`Operators::Number`](../../src/SearchBy/Operators.php) - Any operator for this type will be available for `Int` and `Float`.
* `SearchByOperatorsNull` / [`Operators::Null`](../../src/SearchBy/Operators.php) - Additional operators available for nullable fields.
* `SearchByOperatorsExtra` / [`Operators::Extra`](../../src/SearchBy/Operators.php) - List of additional extra operators for all types.
* `SearchByOperatorsEnum` / [`Operators::Enum`](../../src/SearchBy/Operators.php) - Default operators for enums.
* `SearchByOperatorsDisabled` / [`Operators::Disabled`](../../src/SearchBy/Operators.php) - Disabled operators.

### GraphQL (recommended)

```graphql
extend scalar SearchByOperatorsEnum
@searchByExtendOperators                    # Re-use operators for `SearchByOperatorsEnum` from config
@searchByExtendOperators(type: "MyScalar")  # Re-use operators from `MyScalar` from schema

scalar MyScalar
@scalar(class: "App\\GraphQL\\Scalars\\MyScalar")
@searchByExtendOperators                    # Re-use operators for `MyScalar` from config
@searchByExtendOperators(type: "MyScalar")  # same
@searchByExtendOperators(type: "Int")       # Re-use operators from `Int` from schema
@searchByOperatorEqual                      # Add package operator
@myOperator                                 # Add custom operator
```

Keep in mind, when you define/extend the scalar/enum, it will override all existing operators, so if you just want to add new operators, the `@searchByExtendOperators` directive should be used.

[include:graphql-directive]: @searchByExtendOperators
[//]: # (start: df58b83a1ba68755665e0657a0ec9e4040fa2887c91de730680491d84e9e3251)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Extends the list of operators by the operators from the specified
`type` or from the config if `null`.
"""
directive @searchByExtendOperators(
    type: String
)
on
    | ENUM
    | SCALAR
```

[//]: # (end: df58b83a1ba68755665e0657a0ec9e4040fa2887c91de730680491d84e9e3251)

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
