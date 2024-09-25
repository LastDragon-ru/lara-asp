# Upgrade Guide

[include:file]: ../../docs/Shared/Upgrade.md
[//]: # (start: preprocess/aa9fc458898c7c1c)
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

[//]: # (end: preprocess/aa9fc458898c7c1c)

## Tips

> [!TIP]
>
> Maybe a good idea to add test (at least) with [`GraphQLAssertions::assertGraphQLSchemaEquals()`][code-links/c84a35ff75f6a95e] assertion before the upgrade 🤗

# Upgrade from v6

## General

[include:file]: ../../docs/Shared/Upgrade/FromV6.md
[//]: # (start: preprocess/9679e76379216855)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] PHP 8.1 is not supported anymore. Migrate to the newer version.

* [ ] Direct usages of `Container::getInstances()` were replaced by explicit constructor parameters. You may need to update your code accordingly (#151).

[//]: # (end: preprocess/9679e76379216855)

* [ ] The [`JsonStringType`][code-links/9ad31c571587f0f4] is not implement [`TypeDefinition`][code-links/3c9ddc100b69df14] anymore. To add the scalar into the Schema, you can use `@type`/`@scalar` directive, or create a custom implementation of `TypeDefinition` contract to use with `Builder`/`Manipulator`.

## Tests

* [ ] Following traits required `app()` method to get access to the Container (#151)
  * [`GraphQLAssertions`][code-links/a6029821bb9d8f2e]

  ```php
  protected function app(): Application {
      return $this->app;
  }
  ```

## API

This section is actual only if you are extending the package. Please review and update (listed the most significant changes only):

* [ ] [`TypeDefinition::getTypeDefinition()`][code-links/ac7acd6cc65a080d] return type changed.

* [ ] `💀\LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithManipulator` removed, create instance by hand instead (reason #151).

# Upgrade from v5

## General

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: preprocess/2e85dad2b0618274)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: preprocess/2e85dad2b0618274)

* [ ] [Input type auto-generation](README.md#input-type-auto-generation) reworked and may include more/fewer fields. Please check the documentation and update the schema if needed.

## `@searchBy`

* [ ] `enum SearchByTypeFlag { yes }` => `enum SearchByTypeFlag { Yes }`. 🤝

* [ ] `@searchByOperators` => `@searchByExtendOperators`. 🤝

* [ ] `@searchByOperatorRelation` => `@searchByOperatorRelationship` (and class too; generated types will be named as `SearchByRelationship*` instead of `SearchByComplex*`). 🤝

* [ ] `💀\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators::Condition` => [`Operators::Object`][code-links/5f93528c6eb9dc8f].

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

  2. Disable [`SearchByOperatorFieldDirective`][code-links/ab92ab72ccf08721] operator to avoid possible conflict with field names (via schema or config)

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

* [ ] If you are using [`💀FieldResolver`][code-links/c4fffbfe6bcac46f], use [`BuilderFieldResolver`][code-links/372c362d9f824e7f] instead. 🤝

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

  2. Disable [`SortByOperatorFieldDirective`][code-links/b26bb0f7b2034eb1] operator to avoid possible conflict with field names (via schema or config)

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

* [ ] [`\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator`][code-links/93c5d66b7f26a3ec] must explicitly implement concrete [`Scope`][code-links/3972e727bec7c972] (used to filter available directive-operators, previously was required implicitly).

* [ ] [`Handler`][code-links/69acaf2657f34907]

* [ ] [`\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator`][code-links/93c5d66b7f26a3ec]

* [ ] [`TypeDefinition`][code-links/3c9ddc100b69df14].

* [ ] [`TypeProvider`][code-links/95d8c0cb57870603]

* [ ] [`TypeSource`][code-links/8462c350ae3f58ec]

* [ ] `💀\LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties` => [`ConditionTooManyFields`][code-links/89331c0547a570ec]

* [ ] `💀\LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeUnknown` removed

* [ ] [`💀Property`][code-links/7c84e0ec8e3ddcb3] => [`Field`][code-links/2cd9d43238896ed8]

* [ ] [`Manipulator`][code-links/3027be4084984b5a]

  * [ ] `BuilderInfo` removed. To get `BuilderInfo` instance within Operator the [`Context`][code-links/4da92573cf155b67] should be used instead

    ```php
    $context->get(LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextBuilderInfo::class)?->value
    ```

  * [ ] `getPlaceholderTypeDefinitionNode()` removed => [`AstManipulator::getOriginType()`][code-links/947cddaaef5e3f1b]

  * [ ] `getTypeOperators()`/`getOperator()` removed. To get operators the [`Context`][code-links/4da92573cf155b67] should be used instead

    ```php
    $context->get(LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextOperators::class)?->value
    ```

* [ ] [`HandlerDirective`][code-links/e547fd7224724e03]

* [ ] `💀\LastDragon_ru\LaraASP\GraphQL\Builder\Directives\PropertyDirective` removed

* [ ] [`Operators`][code-links/786d31a251fa3c1e]

* [ ] `LastDragon_ru\LaraASP\GraphQL\Builder\Sources\*`

* [ ] `💀\LastDragon_ru\LaraASP\GraphQL\Builder\Traits\PropertyOperator` => [`HandlerOperator`][code-links/e0d23d0df71d20ae]

* [ ] [`InputObject`][code-links/6c3b5e426f3af114]

* [ ] `LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\*` => `LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters\*`

* [ ] `💀\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator` => [`\LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Operator`][code-links/107e39e7d99461b2]

* [ ] `💀\LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\BaseOperator` => [`\LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Operator`][code-links/107e39e7d99461b2]

* [ ] `💀\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits\ScoutSupport` => [`WithScoutSupport`][code-links/f7fa8cd7df69c5a4]

* [ ] `@searchByOperatorCondition` => `@searchByOperatorChild` (and class too)

* [ ] `@searchByOperatorProperty` => `@searchByOperatorCondition` (and class too)

* [ ] `@sortByOperatorProperty` => `@sortByOperatorChild` (and class too)

* [ ] `@sortByOperatorField` => `@sortByOperatorSort` (and class too)

* [ ] `💀\LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\StreamFactory::enhance()` removed

* [ ] [`Directive`][code-links/bed52c4a6cb03cac]

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/372c362d9f824e7f]: src/Builder/Contracts/BuilderFieldResolver.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver"

[code-links/4da92573cf155b67]: src/Builder/Contracts/Context.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context"

[code-links/69acaf2657f34907]: src/Builder/Contracts/Handler.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler"

[code-links/93c5d66b7f26a3ec]: src/Builder/Contracts/Operator.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator"

[code-links/3972e727bec7c972]: src/Builder/Contracts/Scope.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope"

[code-links/c4fffbfe6bcac46f]: src/Builder/Contracts/Scout/FieldResolver.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver"

[code-links/3c9ddc100b69df14]: src/Builder/Contracts/TypeDefinition.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition"

[code-links/ac7acd6cc65a080d]: src/Builder/Contracts/TypeDefinition.php#L18-L28 "\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition::getTypeDefinition()"

[code-links/95d8c0cb57870603]: src/Builder/Contracts/TypeProvider.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider"

[code-links/8462c350ae3f58ec]: src/Builder/Contracts/TypeSource.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource"

[code-links/e547fd7224724e03]: src/Builder/Directives/HandlerDirective.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Directives\HandlerDirective"

[code-links/89331c0547a570ec]: src/Builder/Exceptions/Client/ConditionTooManyFields.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyFields"

[code-links/2cd9d43238896ed8]: src/Builder/Field.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Field"

[code-links/3027be4084984b5a]: src/Builder/Manipulator.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator"

[code-links/786d31a251fa3c1e]: src/Builder/Operators.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Operators"

[code-links/7c84e0ec8e3ddcb3]: src/Builder/Property.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Property"

[code-links/e0d23d0df71d20ae]: src/Builder/Traits/HandlerOperator.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator"

[code-links/f7fa8cd7df69c5a4]: src/Builder/Traits/WithScoutSupport.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithScoutSupport"

[code-links/6c3b5e426f3af114]: src/Builder/Types/InputObject.php "\LastDragon_ru\LaraASP\GraphQL\Builder\Types\InputObject"

[code-links/9ad31c571587f0f4]: src/Scalars/JsonStringType.php "\LastDragon_ru\LaraASP\GraphQL\Scalars\JsonStringType"

[code-links/ab92ab72ccf08721]: src/SearchBy/Definitions/SearchByOperatorFieldDirective.php "\LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorFieldDirective"

[code-links/5f93528c6eb9dc8f]: src/SearchBy/Operators.php#L60 "\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators::Object"

[code-links/b26bb0f7b2034eb1]: src/SortBy/Definitions/SortByOperatorFieldDirective.php "\LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorFieldDirective"

[code-links/107e39e7d99461b2]: src/SortBy/Operators/Operator.php "\LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Operator"

[code-links/bed52c4a6cb03cac]: src/Stream/Directives/Directive.php "\LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive"

[code-links/a6029821bb9d8f2e]: src/Testing/GraphQLAssertions.php "\LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions"

[code-links/c84a35ff75f6a95e]: src/Testing/GraphQLAssertions.php#L90-L102 "\LastDragon_ru\LaraASP\GraphQL\Testing\GraphQLAssertions::assertGraphQLSchemaEquals()"

[code-links/947cddaaef5e3f1b]: src/Utils/AstManipulator.php#L298-L339 "\LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator::getOriginType()"

[//]: # (end: code-links)
