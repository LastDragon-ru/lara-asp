# `@type`

Converts scalar into GraphQL Type. Similar to Lighthouse's `@scalar` directive, but uses Laravel Container to resolve instance and also supports PHP enums.

[include:graphql-directive]: @type
[//]: # (start: 9cc8aa297d4585260951ac6341e80b5161cd70bef8eefb1ad877eded0553f6d5)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Converts scalar into GraphQL Type. Similar to Lighthouse's `@scalar`
directive, but uses Laravel Container to resolve instance and also
supports PHP enums.
"""
directive @type(
    """
    Reference to a PHP Class/Enum (FQN).

    If not PHP Enum, the Laravel Container with the following additional
    arguments will be used to resolver the instance:

    * `string $name` - the type name.
    * `GraphQL\Language\AST\ScalarTypeDefinitionNode $node` - the AST node.
    * `array&ScalarConfig $config` - the scalar configuration (if `GraphQL\Type\Definition\ScalarType`).

    Resolved instance must be an `GraphQL\Type\Definition\Type&GraphQL\Type\Definition\NamedType` and have a name equal
    to `$name` argument.
    """
    class: String!
)
on
    | SCALAR
```

[//]: # (end: 9cc8aa297d4585260951ac6341e80b5161cd70bef8eefb1ad877eded0553f6d5)
