# Upgrade Guide

[include:file]: ../../docs/Shared/Upgrade.md
[//]: # (start: c70a9a43c0a80bd2e7fa6010a9b2c0fbcab4cb4d536d7a498216d9df7431f7e2)
[//]: # (warning: Generated automatically. Do not edit.)

## Instructions

1. Determine the current version (`composer info ...`)
2. Choose the wanted version
3. Follow the instructions
4. ??????
5. PROFIT

For example, if the current version is `2.x` and you want to migrate to `5.x`, you need to perform all steps in the following order:

* "Upgrade from v2"
* "Upgrade from v3"
* "Upgrade from v4"

Please also see [changelog](https://github.com/LastDragon-ru/lara-asp/releases) to find all changes.

## Legend

| 🤝 | Backward-compatible change. Please note that despite you can ignore it now, but it will be mandatory in the future. |
|:--:|:--------------------------------------------------------------------------------------------------------------------|

[//]: # (end: c70a9a43c0a80bd2e7fa6010a9b2c0fbcab4cb4d536d7a498216d9df7431f7e2)

# Upgrade from v5

## General

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: fd146cf51ef5a8d9d13e0317c09860f472c63cb3d60d02f4d95deb3e12cae73d)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: fd146cf51ef5a8d9d13e0317c09860f472c63cb3d60d02f4d95deb3e12cae73d)

## `@searchBy`

* [ ] `enum SearchByTypeFlag { yes }` => `enum SearchByTypeFlag { Yes }`. 🤝

## `@sortBy`

* [ ] `enum SortByTypeFlag { yes }` => `enum SortByTypeFlag { Yes }`. 🤝

* [ ] `enum SortByTypeDirection { asc, desc }` => `enum SortByTypeDirection { Asc, Desc }`. 🤝

* [ ] If you are testing generated queries, you need to update `sort_by_*` alias to `lara_asp_graphql__sort_by__*`.

* [ ] If you are overriding Extra operators, you may need to add `SortByOperators::Extra` to use new built-in:

    ```php
    $settings = [
        'sort_by'   => [
            'operators' => [
                SortByOperators::Extra => [
                    SortByOperators::Extra,
                    SortByOperatorRandomDirective::class,
                ],
            ],
        ],
    ];
    ```

* [ ] If you are using `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver`, use `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderPropertyResolver` instead. 🤝

## API

This section is actual only if you are extending the package. Please review and update (listed the most significant changes only):

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition`.

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator` (removed `BuilderInfo`)

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Directives\PropertyDirective`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Types\InputObject`

* [ ] `LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\*` => `LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\*`

* [ ] To get `BuilderInfo` instance within Operator the `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context` should be used instead of `LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator`:

    ```php
    $context->get(LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerContextBuilderInfo::class)?->value
    ```
