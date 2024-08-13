# `@stream` 🧪

Unlike the `@paginate` (and similar) directive, the `@stream` provides a uniform way to perform Offset/Limit and Cursor pagination of Eloquent/Query/Scout builders. Filtering and sorting enabled by default via [`@searchBy`](@searchBy.md) and [`@sortBy`](@sortBy.md) directives.

> [!NOTE]
>
> The directive is experimental. The true cursor pagination is not implemented yet, the limit/offset is used internally. Any feedback would be greatly appreciated.

[include:graphql-directive]: @stream
[//]: # (start: c09383f6b6438dd4cd88c44c00e6b6471ef4fc6e0180e6389dfd281c0d3e8ded)
[//]: # (warning: Generated automatically. Do not edit.)

```graphql
"""
Splits list of items into the chunks and returns one chunk specified
by an offset or a cursor.
"""
directive @stream(
    """
    Overrides default searchable status.
    """
    searchable: Boolean

    """
    Overrides default sortable status.
    """
    sortable: Boolean

    """
    Overrides default builder. Useful if the standard detection
    algorithm doesn't fit/work. By default, the directive will use
    the field and its type to determine the Builder to query.
    """
    builder: StreamBuilder

    """
    Overrides default limit.
    """
    limit: Int

    """
    Overrides default unique key. Useful if the standard detection
    algorithm doesn't fit/work. By default, the directive will use
    the name of field with `ID!` type.
    """
    key: String
)
on
    | FIELD_DEFINITION

"""
Explicit builder. Only one of fields allowed.
"""
input StreamBuilder {
    """
    The reference to a function that provides a Builder instance.
    """
    builder: String

    """
    The class name of the model to query.
    """
    model: String

    """
    The relation name to query.
    """
    relation: String
}
```

[//]: # (end: c09383f6b6438dd4cd88c44c00e6b6471ef4fc6e0180e6389dfd281c0d3e8ded)

## Motivation

Out the box Laravel and so Lighthouse supporting the following pagination types:

* Page pagination (default) - page/size pagination with counting
* Simple pagination - page/size, but without counting at all
* Cursor pagination - previous/next only

Probably still most used "Page pagination" is always performing counting of items, even if the count of items is not needed and not queried. For huge datasets counting may be extremely slow, especially with filtering/sorting. In modern single-page application (SPA) we can query `count` only ones to render pagination and just navigate between pages after.

Why not "Simple pagination" that does not perform counting? Well, Lighthouse (that just utilizes Laravel APIs) does not provide a way to get a total count of items, unfortunately. Moreover, it still will use limit/offset, which is usually slower than the Cursor.

Another edge case, all paginator types has a different GraphQL types and thus cannot be used in the same query. This is means that if you need Page and Cursor pagination, you will need to create two fields.

Also, there is no Offset/Limit pagination out the box, that may be preferred over page/size in some cases.

## How to use

Schema:

```graphql
type Query {
    test: [Object!]! @stream
}

type Object {
    id: ID!
    value: String
}
```

Query:

```graphql
query example(
    $limit: Int!,
    $offset: StreamOffset,
    $where: SearchByRootObject,
    $order: [SortByRootObject!],
) {
    objects(where: $where, order: $order, limit: $limit, offset: $offset) {
        items {
            id
            value
        }
        length
        navigation {
            previous
            current
            next
        }
    }
}
```

Offset/Limit pagination:

```json
{
    "limit": 10,
    "offset": 5,
    "where": null,
    "order": null
}
```

Cursor pagination:

```json
{
    "limit": 10,
    "offset": "... cursor string ...",
    "where": null,
    "order": null
}
```

## Builder precedence

* Explicit via `builder` argument
* Query/Type/Resolver (see [resolver precedence](https://lighthouse-php.com/master/the-basics/fields.html#resolver-precedence))
* Model (for the root query)
* Relationship (based on field and type names)

## Scout

To use Scout, you just need to add `@search` to an argument, the same as for `@paginate`.

```graphql
type Query {
    test(search: String! @search): [Object!]! @stream
}

type Object {
    id: ID!
    value: String
}
```

Keep in mind:

* There is no way to use limit/offset, so the directive converts them into page/size and then slice results
* Some engines may perform counting (seems actual for `Database` only)
