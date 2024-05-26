# Upgrade Guide

[include:file]: ../../docs/Shared/Upgrade.md
[//]: # (start: 3fcf292b870e49aabe885c416dfaf6c6c66d4571e87d3d3975d9cfe34ea6c9fe)
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

[//]: # (end: 3fcf292b870e49aabe885c416dfaf6c6c66d4571e87d3d3975d9cfe34ea6c9fe)

## Tips

> [!TIP]
>
> Maybe a good idea to add test (at least) with `LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions::assertGraphQLSchemaEquals()` assertion before the upgrade 🤗

# Upgrade from v6

## General

[include:file]: ../../docs/Shared/Upgrade/FromV6.md
[//]: # (start: b0b74ef74f156294a37f3ec42299e221e5e693f3b42297f5cfa79cab99b1df7e)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Direct usages of `Container::getInstances()` were replaced by explicit constructor parameters. You may need to update your code accordingly (#151).

[//]: # (end: b0b74ef74f156294a37f3ec42299e221e5e693f3b42297f5cfa79cab99b1df7e)

* [ ] The `\LastDragon_ru\LaraASP\GraphQL\Scalars\JsonStringType` is not implement `\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition` anymore. To add the scalar into the Schema, you can use `@type`/`@scalar` directive, or create a custom implementation of `TypeDefinition` contract to use with `Builder`/`Manipulator`.

## Tests

* [ ] Following traits required `app()` method to get access to the Container (#151)
  * `\LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions`

  ```php
  protected function app(): Application {
      return $this->app;
  }
  ```

## API

This section is actual only if you are extending the package. Please review and update (listed the most significant changes only):

* [ ] `\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition::getTypeDefinition()` return type changed.

* [ ] `\LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator` removed, create instance by hand instead (reason #151).

# Upgrade from v5

## General

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: 6367638a165291d78965aaeae9ab03b304b0a420eb3f5ad9af0424296cc609ea)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: 6367638a165291d78965aaeae9ab03b304b0a420eb3f5ad9af0424296cc609ea)

* [ ] [Input type auto-generation](README.md#input-type-auto-generation) reworked and may include more/fewer fields. Please check the documentation and update the schema if needed.

## `@searchBy`

* [ ] `enum SearchByTypeFlag { yes }` => `enum SearchByTypeFlag { Yes }`. 🤝

* [ ] `@searchByOperators` => `@searchByExtendOperators`. 🤝

* [ ] `@searchByOperatorRelation` => `@searchByOperatorRelationship` (and class too; generated types will be named as `SearchByRelationship*` instead of `SearchByComplex*`). 🤝

* [ ] `LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators::Condition` => `LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators::Object`.

* [ ] Scalars to add operators were renamed

  * [ ] `SearchByCondition` => `SearchByOperatorsObject`

  * [ ] `SearchByNull` => `SearchByOperatorsNull`

  * [ ] `SearchByExtra` => `SearchByOperatorsExtra`

  * [ ] `SearchByNumber` => `SearchByOperatorsNumber`

  * [ ] `SearchByEnum` => `SearchByOperatorsEnum`

* [ ] Added the root type that will contain only extra operators and newly added `field` operator. The new query syntax is:

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

  If you want to use old query syntax, you need:

  1. Add following bindings into application provider:

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

  2. Disable `LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorFieldDirective` operator to avoid possible conflict with field names (via schema or config)

      ```graphql
      extend scalar SearchByOperatorsDisabled
      @searchByOperatorField
      ```

* [ ] If you define additional operators via `scalar SearchBy*` use `extend scalar SearchBy*` instead (or you will get `TypeDefinitionAlreadyDefined` error).

## `@sortBy`

* [ ] `enum SortByTypeFlag { yes }` => `enum SortByTypeFlag { Yes }`. 🤝

* [ ] `enum SortByTypeDirection { asc, desc }` => `enum SortByTypeDirection { Asc, Desc }`. 🤝

* [ ] If you are testing generated queries, you need to update `sort_by_*` alias to `lara_asp_graphql__sort_by__*`.

* [ ] If you are overriding Extra operators, you should to add `SortByOperators::Extra` to use new built-in:

  ```graphql
  extend scalar SortByOperatorsExtra
  @sortByExtendOperators
  @sortByOperatorRandom
  ```

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

* [ ] Added the root type that will contain only extra operators and newly added `field` operator. The new query syntax is:

  ```graphql
  query {
      # ORDER BY user.name ASC, text DESC
      comments(order: [
          {field: {user: {name: asc}}}
          {field: {text: desc}}
      ])
  }
  ```

  If you want to use old query syntax, you need:

  1. Add following bindings into application provider:

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

  2. Disable `LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorFieldDirective` operator to avoid possible conflict with field names (via schema or config)

      ```graphql
      extend scalar SortByOperatorsDisabled
      @sortByOperatorField
      ```

* [ ] `@sortByOperatorRandom` cannot be added to `FIELD_DEFINITION` anymore.

* [ ] If you define addition operators via `scalar SortBy*` use `extend scalar SortBy*` instead (or you will get `TypeDefinitionAlreadyDefined` error).

* [ ] Scalars to add operators were renamed

  * [ ] `SortByExtra` => `SortByOperatorsExtra`

## API

This section is actual only if you are extending the package. Please review and update (listed the most significant changes only):

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator` must explicitly implement concrete `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope` (used to filter available directive-operators, previously was required implicitly).

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition`.

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties` => `LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyFields`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeUnknown` removed

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Property` => `LastDragon_ru\LaraASP\GraphQL\Builder\Field`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator`

  * [ ] `BuilderInfo` removed. To get `BuilderInfo` instance within Operator the `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context` should be used instead

    ```php
    $context->get(LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextBuilderInfo::class)?->value
    ```

  * [ ] `getPlaceholderTypeDefinitionNode()` removed => `LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator::getOriginType()`

  * [ ] `getTypeOperators()`/`getOperator()` removed. To get operators the `LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context` should be used instead

    ```php
    $context->get(LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextOperators::class)?->value
    ```

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective`

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Directives\PropertyDirective` removed

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Operators`

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
