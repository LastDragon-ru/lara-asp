# Eloquent Helpers

This package contains useful extensions and mixins for [Eloquent](https://laravel.com/docs/eloquent).

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |  `HEAD`  ,  `5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^10.0.0` |   `HEAD ⋯ 2.1.0`   |
|  | `^9.21.0` |   `HEAD ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "eloquent"}})
[//]: # (start: 1c6aa4607fbf4f4c0efb2249a42271075a45316e15a4fe7f914d8bb883f71ea2)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-eloquent
```

[//]: # (end: 1c6aa4607fbf4f4c0efb2249a42271075a45316e15a4fe7f914d8bb883f71ea2)

# Iterators

Iterators are similar to `Builder::chunk()` but uses generators instead of `\Closure` that makes code more readable:

```php
$query = \App\Models\User::query();

$query->chunk(100, function ($users) {
    foreach ($users as $user) {
        // ...
    }
});

foreach ($query->getChunkedIterator() as $user) {
    // ...
}
```

Iterators also support limit/offset, by default it will try to get them from the Builder, but you can also set them by hand:

```php
$query = \App\Models\User::query()->offset(10)->limit(20);

foreach ($query->getChunkedIterator() as $user) {
    // ... 20 items from 10
}

foreach ($query->getChunkedIterator()->setOffset(0) as $user) {
    // ...20 items from 0
}
```

When you use the default [`ChunkedIterator`](./src/Iterators/ChunkedIterator.php) you should not modify/delete the items while iteration or you will get unexpected results (eg missing items). If you need to modify/delete items while iteration you can use [`ChunkedChangeSafeIterator`](./src/Iterators/ChunkedChangeSafeIterator.php) that specially created for this case and unlike standard `chunkById()` it is always safe (please see <https://github.com/laravel/framework/issues/35400> for more details). But there are few limitations:

* it is not possible to sort rows, they always will be sorted by `column asc`;
* the `column` should not be changed while iteration or this may lead to repeating row in results;
* the row inserted while iteration may be skipped if it has `column` with the value that lover than the internal pointer;
* queries with UNION is not supported;
* `offset` from Builder will not be used;

To create a change safe instance you can use:

```php
$query = \App\Models\User::query();

foreach ($query->getChangeSafeIterator() as $user) {
    // ...
}
```

# Mixins

## `\Illuminate\Database\Eloquent\Builder`

| Name                                    | Description                                                                                   |
|-----------------------------------------|-----------------------------------------------------------------------------------------------|
| `orderByKey(string $direction = 'asc')` | Add an `ORDER BY primary_key` clause to the query.                                            |
| `orderByKeyDesc()`                      | Alias of `orderByKey('desc')`                                                                 |
| `getChunkedIterator()`                  | Return [`ChunkedIterator`](./src/Iterators/ChunkedIterator.php) instance.                     |
| `getChangeSafeIteratorIterator()`       | Return [`ChunkedChangeSafeIterator`](./src/Iterators/ChunkedChangeSafeIterator.php) instance. |

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: 057ec3a599c54447e95d6dd2e9f0f6a6621d9eb75446a5e5e471ba9b2f414b89)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 057ec3a599c54447e95d6dd2e9f0f6a6621d9eb75446a5e5e471ba9b2f414b89)
