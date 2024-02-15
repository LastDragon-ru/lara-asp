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

## Tips

> [!TIP]
>
> Maybe a good idea to add test (at least) with `LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions::assertGraphQLSchemaEquals()` assertion before the upgrade 🤗

# Upgrade from v5

## General

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: fd146cf51ef5a8d9d13e0317c09860f472c63cb3d60d02f4d95deb3e12cae73d)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: fd146cf51ef5a8d9d13e0317c09860f472c63cb3d60d02f4d95deb3e12cae73d)

* [ ] [Input type auto-generation](README.md#input-type-auto-generation) reworked and may include more/fewer fields. Please check the documentation and update the schema if needed.

## `@searchBy`

* [ ] `enum SearchByTypeFlag { yes }` => `enum SearchByTypeFlag { Yes }`. 🤝

* [ ] `@searchByOperatorRelation` => `@searchByOperatorRelationship` (and class too; generated types will be named as `SearchByRelationship*` instead of `SearchByComplex*`).

* [ ] `LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators::Condition` => `LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators::Object`.

* [ ] `scalar SearchByCondition` => `scalar SearchByObject`.

* [ ] Added the root type that will contain only extra operators and newly added `field` operator (always present and cannot be removed). The new query syntax is:

  ```graphql
  query {
      # WHERE name = "LastDragon"
      users(where: {
          field: { name: {equal: "LastDragon"} }
      }) {
          id
      }
  }
  ```

  If you want to use old query syntax, you can add following bindings into application provider:

  ```php
  $this->app->bind(
      LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition\Root::class,
      LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition\V5::class,
  );
  $this->app->bind(
      LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition\Condition::class,
      LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition\V5::class,
  );
  ```

## `@sortBy`

* [ ] `enum SortByTypeFlag { yes }` => `enum SortByTypeFlag { Yes }`. 🤝

* [ ] `enum SortByTypeDirection { asc, desc }` => `enum SortByTypeDirection { Asc, Desc }`. 🤝

* [ ] If you are testing generated queries, you need to update `sort_by_*` alias to `lara_asp_graphql__sort_by__*`.

* [ ] If you are overriding Extra operators, you should to add `SortByOperators::Extra` to use new built-in:

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

* [ ] If you are using `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver`, use `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver` instead. 🤝

* [ ] Added the root type that will contain only extra operators and newly added `field` operator (always present and cannot be removed). The new query syntax is:

  ```graphql
  query {
      # ORDER BY user.name ASC, text DESC
      comments(order: [
          {field: {user: {name: asc}}}
          {field: {text: desc}}
      ])
  }
  ```

  If you want to use old query syntax, you can add following bindings into application provider:

  ```php
  $this->app->bind(
      LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause\Root::class,
      LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause\V5::class,
  );
  $this->app->bind(
      LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause\Clause::class,
      LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause\V5::class,
  );
  ```

* [ ] `@sortByOperatorRandom` cannot be added to `FIELD_DEFINITION` anymore.

## API

This section is actual only if you are extending the package. Please review and update (listed the most significant changes only):

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition`.

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties` => `LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyFields`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Property` => `LastDragon_ru\LaraASP\GraphQL\Builder\Field`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator`

  * [ ] `BuilderInfo` removed. To get `BuilderInfo` instance within Operator the `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context` should be used instead

    ```php
    $context->get(LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextBuilderInfo::class)?->value
    ```

  * [ ] `getPlaceholderTypeDefinitionNode()` removed => `LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator::getOriginType()`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Directives\PropertyDirective` removed

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Sources\*`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Traits\PropertyOperator` => `LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Types\InputObject`

* [ ] `LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\*` => `LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\*`

* [ ] `LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator` => `LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Operator`

* [ ] `LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\BaseOperator` => `LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Operator`

* [ ] `LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits\ScoutSupport` => `LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithScoutSupport`

* [ ] `@searchByOperatorCondition` => `@searchByOperatorChild` (and class too)

* [ ] `@searchByOperatorProperty` => `@searchByOperatorCondition` (and class too)

* [ ] `@sortByOperatorProperty` => `@sortByOperatorChild` (and class too)

* [ ] `@sortByOperatorField` => `@sortByOperatorSort` (and class too)

* [ ] `LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\StreamFactory::enhance()` removed

* [ ] `LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive`
