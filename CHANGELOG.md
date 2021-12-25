## [0.10.0](https://github.com/LastDragon-ru/lara-asp/compare/0.9.0...0.10.0) (2021-12-25)

| ☣    | Breaking changes |
|:-----:|:-----------------|

### Features

* **core:** `Observer` implementation. ([9bf13f9](https://github.com/LastDragon-ru/lara-asp/commit/9bf13f9e8d8df201e7decda66cb3f79d2dae731f))
* **eloquent:** Added `Iterator::$index` that required to count items to continue iteration. ([cd3be87](https://github.com/LastDragon-ru/lara-asp/commit/cd3be875dab4b51286fe414b6bb4210bd94c6b5f))
* **eloquent:** Added `ModelHelper::isRelation()`. ([b621e97](https://github.com/LastDragon-ru/lara-asp/commit/b621e97b440d92dcbe829a2ad8a28e740682eecc))
* **eloquent:** Iterators will use `Subject` (so `onBeforeChunk()` and `onAfterChunk()` will not redefine existing callback). ([60858d3](https://github.com/LastDragon-ru/lara-asp/commit/60858d32616161f30a75c8539f00a5aca4392c06)) ☣
* **eloquent:** Removed `ChunkedIterator::safe()`. ([846de72](https://github.com/LastDragon-ru/lara-asp/commit/846de72c6f865986226a53fdde7f492b7f27cc69)) ☣
* **formatter:** `Formatter::app()` renamed to `Formatter::getApplication()`. ([6f50747](https://github.com/LastDragon-ru/lara-asp/commit/6f50747aa5bb1dd64820814ee39b4bd8b37db3e7)) ☣
* **formatter:** Added `Formatter::forTimezone()` to create formatter for specific timezone, also, default timezone set to `null` instead of `UTC`. ([41bc6af](https://github.com/LastDragon-ru/lara-asp/commit/41bc6af7b1c6417c185feeb2a5c7da9ebb97baba)) ☣
* **formatter:** Better logic for settings locale: by default locale will be `null` and `getDefaultLocale()` will be used. ([dd94a28](https://github.com/LastDragon-ru/lara-asp/commit/dd94a284667ef6a613830841a117272db3770d28))
* **graphql:** New testing helper `GraphQLAssertions::useGraphQLSchema()`. ([376425c](https://github.com/LastDragon-ru/lara-asp/commit/376425c90ef3459bd7329f96084b98b5811913cf))
* **testing:** `SetUpTraits` deprecated, `@before`/`afterApplicationCreated()`/`beforeApplicationDestroyed()` can be used instead. ([fee2f29](https://github.com/LastDragon-ru/lara-asp/commit/fee2f2972e024712167e23fefb8eae174c988200)) ☣
* **testing:** New constraint `MimeType` that checks that response has a `Content-Type` header by given file extension. ([9462814](https://github.com/LastDragon-ru/lara-asp/commit/94628145003afbb705fb85618b22086e2667a6b5))


### Bug Fixes

* **graphql:** `@sortBy` will check `FieldResolver` only for `_` (type). ([3b4acc2](https://github.com/LastDragon-ru/lara-asp/commit/3b4acc25460566a0c3f730fe7de7deec51713e89))
* **graphql:** Enums serialization. ([2dd62a7](https://github.com/LastDragon-ru/lara-asp/commit/2dd62a7e980616072e74e3dd66054c6286116a17)) ☣
* **queue**: "Cron\CronExpression::__construct(): Argument \#1 ($expression) must be of type string, null given". ([d936445](https://github.com/LastDragon-ru/lara-asp/commit/d93644588ada578960fb1b08e8eff00265899b7a))
* **testing:** `CronableAssertions::assertCronableRegistered()` will work even if no `cron` defined for the job. ([08a47dc](https://github.com/LastDragon-ru/lara-asp/commit/08a47dc0340f59ae89cda7c610e64fa53f051833))


## [0.9.0](https://github.com/LastDragon-ru/lara-asp/compare/0.8.1...0.9.0) (2021-10-24)

☣ | Breaking changes
:---: | :---

### Features

* `guzzlehttp/psr7:^2.0` support. ([c33899d](https://github.com/LastDragon-ru/lara-asp/commit/c33899d900a4498ed0ee79bef334825bb0d167bb))
* **eloquent,graphql:** `ModelHelper` moved into `eloquent` package. ([34d99ed](https://github.com/LastDragon-ru/lara-asp/commit/34d99ed7a43aee6e8ad3f26145d53d8ff9fd5d6d)) ☣
* **eloquent:** `EloquentBuilder::orderByKey()` mixin will use qualified key name. ([155dacb](https://github.com/LastDragon-ru/lara-asp/commit/155dacbc1f43b494ae9c68234421f1a5a7f08171))
* **eloquent:** `ModelHelper::getRelation()` will throw `PropertyIsNotRelation` instead of `LogicException`. ([7f350a9](https://github.com/LastDragon-ru/lara-asp/commit/7f350a9782a276edb5092fd46be4c5d481998816))
* **graphql:** `@searchBy` will support types from `TypeRegistry`. ([1a92006](https://github.com/LastDragon-ru/lara-asp/commit/1a9200654e3f38c5cb99ad6dd6738f1f83f45670)) ☣
* **graphql:** `@searchBy`: new operators for `String`: `contains`, `startsWith`, `endsWith`. ([f2f44b7](https://github.com/LastDragon-ru/lara-asp/commit/f2f44b77517f25c7f7e242ae8db123be79089fa2))
* **graphql:** `@sortBy` support input type auto-generation by existing `type`. ([06da4a7](https://github.com/LastDragon-ru/lara-asp/commit/06da4a77ee5d84cbace4ee385986d52f20848427))
* **graphql:** `@sortBy` will use dependent subqueries instead of joins. ([a1e4608](https://github.com/LastDragon-ru/lara-asp/commit/a1e4608c83efdc5035eb0c58b7ae7389c9ab2c08)) ☣
* **graphql:** New directive `sortByUnsortable` that allow exclude fields from sort. ([10a39ab](https://github.com/LastDragon-ru/lara-asp/commit/10a39ab221a443885467ee84f78ddbe041afc0eb))
* **queue:** `CronableRegistrator` will use `PendingDispatch` (so `ShouldBeUnique` should work now). ([6c4cbd0](https://github.com/LastDragon-ru/lara-asp/commit/6c4cbd0ce20fea1e5143a9b502ebc9ec0194096e))
* **queue:** `Dispatchable::run()` will use `dispatchSync()` instead of `dispatchNow()`. ([50ecb56](https://github.com/LastDragon-ru/lara-asp/commit/50ecb568186342a0a14aa9c2c0fdcd38da0ba4ce)) ☣
* **queue:** Injection of `QueueableConfigurator` into `__construct()` not needed anymore, `Container::afterResolving()` will be used instead. ([aebffad](https://github.com/LastDragon-ru/lara-asp/commit/aebffad5d8c8eaddfea02305025aaa51a0844ea2))
* **testing:** New assertion: `assertDatabaseQueryEquals()`. ([64fa090](https://github.com/LastDragon-ru/lara-asp/commit/64fa090f9558da4cb298eb534482630679777184))


### Bug Fixes

* **eloquent:** Fixed "Integrity constraint violation: 1052 Column 'id' in order clause is ambiguous" for `ChunkedChangeSafeIterator` (it will use qualified column name for default). ([5695af5](https://github.com/LastDragon-ru/lara-asp/commit/5695af5d6d24f067eba6f4173f33ec4b1e5dbd19))


### Code Refactoring

* **graphql:** `LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\ScoutColumnResolver` renamed to `\LastDragon_ru\LaraASP\GraphQL\SortBy\Builders\Scout\ColumnResolver` ([339cf58](https://github.com/LastDragon-ru/lara-asp/commit/339cf58ae9b3d9f7aae39e4838fbce9b962bf444)) ☣


### [0.8.1](https://github.com/LastDragon-ru/lara-asp/compare/0.8.0...0.8.1) (2021-09-11)


### Bug Fixes

* **testing:** `CronableAssertions::assertCronableRegistered()` will correctly check registration of `Cronable`. ([a2437db](https://github.com/LastDragon-ru/lara-asp/commit/a2437dbe9eb402b54c9a1992ca2246433d45374d))

## [0.8.0](https://github.com/LastDragon-ru/lara-asp/compare/0.7.0...0.8.0) (2021-09-05)

☣ | Breaking changes
:---: | :---

### Features

* **core:** Package `Translator` allows to specify default translation (will be used if the translation string doesn't exist). ([c9e1e5d](https://github.com/LastDragon-ru/lara-asp/commit/c9e1e5d0fc36e11686c07fc7b712474c7d3cd83d))
* **eloquent:** New trait `WithDateSerialization` that will serialize dates that implements `JsonSerializable` by `JsonSerializable::jsonSerialize()` instead of hardcoded `Carbon::toJSON()`. ([085fc47](https://github.com/LastDragon-ru/lara-asp/commit/085fc4790eb3ee4ba212fce5e3095e22c76f08e9)) ☣
* **graphql:** `Enum` properties will be converted into studly case (to be compatible with PHP Enums). ([3a9e15a](https://github.com/LastDragon-ru/lara-asp/commit/3a9e15a514533672b312a7c88ae47f0c8bf086b7)) ☣
* **graphql:** `ModelHelper::__construct()` accept `class-string<\Illuminate\Database\Eloquent\Model>`. ([e3d92be](https://github.com/LastDragon-ru/lara-asp/commit/e3d92be27e37af6f1c29e51a29ec7998989104a7))
* **queue:** `CronableRegistrator` will use job name as description (= description will not contain settings anymore) and will not add context to log messages. ([04fc1ea](https://github.com/LastDragon-ru/lara-asp/commit/04fc1ea6bdb6865165ec299bcc6329c426b52b3c)) ☣
* **queue:** Added `timezone` setting for `Cronable`. ([8810c7f](https://github.com/LastDragon-ru/lara-asp/commit/8810c7ff34dfbc22c261662274ffca819f38aec9))
* **queue:** Removed `QueueableConfig::Debug`. ([ae9653a](https://github.com/LastDragon-ru/lara-asp/commit/ae9653aa1b62c1ea4e79fe578d5ffa3e91055739)) ☣
* **testing:** Added `WithTranslations` helper that allows replacing translation while tests. ([5e0d4e4](https://github.com/LastDragon-ru/lara-asp/commit/5e0d4e47eca54c0d7fa29947d17641acf422fd11))


### Bug Fixes

* **formatter:** Filesize units (MB => MiB, etc). ([5fc4ba7](https://github.com/LastDragon-ru/lara-asp/commit/5fc4ba70b28195b280d966846cb0f27fdbc2fbca))
* **queue**: TypeError : `LastDragon_ru\LaraASP\Queue\Queueables\Job::LastDragon_ru\LaraASP\Queue\Concerns\{closure}`: Return value must be of type `Illuminate\Foundation\Bus\PendingDispatch`, `null` returned. ([58e2f20](https://github.com/LastDragon-ru/lara-asp/commit/58e2f20fd5f01816e7990504255037c55515dee9))
* **queue:** `CronableRegistrator` removed incorrect realization to check the locked status of the job. ([033de61](https://github.com/LastDragon-ru/lara-asp/commit/033de61607dad2f4c3bcd02585d8bf41b864da42))
* **testing:** `JsonSchemaValue` will not evaluate schema in constructor (regression). ([3c51269](https://github.com/LastDragon-ru/lara-asp/commit/3c512692cf27db85b118f1623af5545be30cd5bd))


## [0.7.0](https://github.com/LastDragon-ru/lara-asp/compare/0.6.1...0.7.0) (2021-08-15)

☣ | Breaking changes
:---: | :---

### Features

* **eloquent:** `iterator()` and `changeSafeIterator()` builder's macros renamed to `getChunkedIterator()` and `getChangeSafeIterator()` accordingly. ([c1140a4](https://github.com/LastDragon-ru/lara-asp/commit/c1140a49ce688241aad701a6c4e67561de4d1447)) ☣
* **eloquent:** Iterators will support offset, `each()` replaced by `onAfterChunk()`, also added `onBeforeChunk()`. ([4276a2c](https://github.com/LastDragon-ru/lara-asp/commit/4276a2c259661296df2ab5f93921c23b4a1aea22)) ☣
* **graphql:** `@searchBy`: short-named operators (`lt`, `lte`, etc) renamed into full form (`lessThan`, etc). ([be2d5f8](https://github.com/LastDragon-ru/lara-asp/commit/be2d5f824fb5642d7ef5dac2081c2ce76e15ac79)) ☣
* **graphql:** `@searchBy`: Relation will use `notExists` instead of `not` + added `exists`. ([63072fa](https://github.com/LastDragon-ru/lara-asp/commit/63072fafa46d6c63eb17d539b5a9f04a98124efd)) ☣
* **grapqhl:** `@sortBy` exceptions rework: each error will have its own exception. Unfortunately, the commit also remove translations support. ([4f99e92](https://github.com/LastDragon-ru/lara-asp/commit/4f99e9215208fb242ddf8c271ccb545d2315f318)) ☣
* **graphql:** `@sortBy` will support types from `TypeRegistry`. ([20f3be5](https://github.com/LastDragon-ru/lara-asp/commit/20f3be5950be758989a9a8da9b9bc998ded16cf2))
* **graphql:** `@sortBy`: Laravel Scout support. ([4c1bb9c](https://github.com/LastDragon-ru/lara-asp/commit/4c1bb9c088266ccb55b528187730664a09998ad1))
* **queue:** `CronableRegistrator` will not dispatch jobs marked as `ShouldBeUnique` if they already dispatched. ([40624e4](https://github.com/LastDragon-ru/lara-asp/commit/40624e4b9ff24652b697e714cf3bf541dce674cd))
* **queue:** Removed DI support for `getQueueConfig()`. ([da57176](https://github.com/LastDragon-ru/lara-asp/commit/da571767429def535681d8f09bdc3d4e6cc47289)) ☣
* **testing:** `CronableAssertions::setQueueableConfig()` will accept instance of `ConfigurableQueueable`. ([cd6430e](https://github.com/LastDragon-ru/lara-asp/commit/cd6430e835dd8fcb84a4e85e961119abfbf3ac81))
* **testing:** `WithQueryLog::getQueryLog()` will accept `\Illuminate\Database\ConnectionResolverInterface`. ([9243d47](https://github.com/LastDragon-ru/lara-asp/commit/9243d47849c016834b21a1213f58f5400e7c9a32))
* **testing:** Added `Override::override()` helper. ([7598390](https://github.com/LastDragon-ru/lara-asp/commit/759839045f9f4e0d97f66e8d53555e5479198c97))
* **testing:** Added a new `WithQueryLog` trait that can work with any connection (the old one marked as deprecated). ([1f7a8c3](https://github.com/LastDragon-ru/lara-asp/commit/1f7a8c3f0a87a42c50d457b43bf70e551f914172))
* **testing:** Removed `TestResponse::getContentType()` macro (not needed for testing). ([ebf2f6d](https://github.com/LastDragon-ru/lara-asp/commit/ebf2f6d6eee31ff1d133253f62104cf18f21c581)) ☣
* **testing:** Removed `TestResponse::toPsrResponse()` macro, `LastDragon_ru\\LaraASP\\Testing\\Constraints\\Response\\Factory::make()` should be used instead. ([7e000da](https://github.com/LastDragon-ru/lara-asp/commit/7e000da6a2820db43e82faf2b1f0cade1ed5de37)) ☣


### Bug Fixes

* **eloquent:** `Iterator::onBeforeChunk()`/`Iterator::onAfterChunk()` will be called only for non-empty chunks. ([140825c](https://github.com/LastDragon-ru/lara-asp/commit/140825c2c59098d8664f88471d053e330c1f4f2c))
* **graphql:** Enums registration moved to `afterResolving` callback. ([2a6288b](https://github.com/LastDragon-ru/lara-asp/commit/2a6288b8f90b584ec17bc2dac32f046c5e4defcc))
* **migrator:** Added missed semicolon to `migration-anonymous.stub`. ([64118fa](https://github.com/LastDragon-ru/lara-asp/commit/64118fa988527329593aefe42e4889994ddbfec8))


### [0.6.1](https://github.com/LastDragon-ru/lara-asp/compare/0.6.0...0.6.1) (2021-06-26)


### Bug Fixes

* **testing:** `JsonSchemaWrapper::__construct()` will correctly handle `JsonSchemaWrapper $schema`. ([85ea05f](https://github.com/LastDragon-ru/lara-asp/commit/85ea05fd3748fcf7c5d13aca6d6360e30d7de7bb))
* **testing:** TypeError : Symfony\Component\HttpFoundation\HeaderUtils::parseQuery(): Argument #1 ($query) must be of type string, null given ([9646143](https://github.com/LastDragon-ru/lara-asp/commit/9646143207dc399e3f0b7452cb52f720f69b22f2))

## [0.6.0](https://github.com/LastDragon-ru/lara-asp/compare/0.5.0...0.6.0) (2021-06-12)


### Features

* **graphql:** `@sortBy`: [HasOneThrough](https://laravel.com/docs/8.x/eloquent-relationships#has-one-through) support 😜 ([558198b](https://github.com/LastDragon-ru/lara-asp/commit/558198b5672757cbb039b6b23211af30e5823b62))
* **testing:** Added `WithTempFile` helper. ([93a24be](https://github.com/LastDragon-ru/lara-asp/commit/93a24be20fc42e64f2962a5077d37d0ea3f0f801))
* **testing:** Signature of `DataProvider::getData()` changed to `DataProvider::getData(bool $raw = false)` to allow nesting DataProviders. ([34f1ff7](https://github.com/LastDragon-ru/lara-asp/commit/34f1ff7b8e4667c93c9fa4e07f167e8061394331))


### Bug Fixes

* **dev:** Added removing special characters from hostname in Vagrantfile. ([1eed300](https://github.com/LastDragon-ru/lara-asp/commit/1eed3003ad1d2a5c8ebc56f60f89c71ab68d70c7))
* **graphql:** `@searchBy` will not advise "contact to developer" for unknown types. ([d9ff32b](https://github.com/LastDragon-ru/lara-asp/commit/d9ff32b6151065b9871d6b46cbadf776b8080590))
* **graphql:** `@sortBy` will not skip "unknown" types and will not convert them into Scalars. ([b5aaccb](https://github.com/LastDragon-ru/lara-asp/commit/b5aaccb981caca25b62fbe08a3b8139abcc74840))
* **testing:** arguments types for `TestResponseMixin::assertJsonMatchesSchema()`. ([46674f0](https://github.com/LastDragon-ru/lara-asp/commit/46674f037958a6a4e66de4e2d69b60d621fc1e58))

## [0.5.0](https://github.com/LastDragon-ru/lara-asp/compare/0.4.0...0.5.0) (2021-05-16)


### Features

* **graphql:** Added two highly powerful `@searchBy` and `@sortBy`  directives for [lighthouse-php](https://lighthouse-php.com/). The `@searchBy` directive provides basic conditions like `=`, `>`, `<`, etc, relations, `not (<condition>)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The `@sortBy` is not only about standard sorting by columns but also allows use relations. 😎
