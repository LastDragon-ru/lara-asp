## [0.14.0](https://github.com/LastDragon-ru/lara-asp/compare/0.13.0...0.14.0) (2022-04-02)

| ☣    | Breaking changes |
|:-----:|:-----------------|

### Features

* **core:** `ProviderWithTranslations` will use `Application::langPath()` instead of hard-coded `resources/` and also expects that package's translations will be places in `lang` directory instead of `resources/lang` (the same as in Laravel v9). ([bc5891d](https://github.com/LastDragon-ru/lara-asp/commit/bc5891d146d5f3bfc2618800f0265e0b7d7347c8)) ☣
* **eloquent:** `Iterator` will support only Eloquent Builder (because is impossible to satisfy phpstan...) ([a871876](https://github.com/LastDragon-ru/lara-asp/commit/a871876c98b7a662c429de4c1c74a96ec4c5a31c)) ☣
* **graphql/`@sortBy`:** `BelongsToMany` and `MorphToMany` support. ([650daf7](https://github.com/LastDragon-ru/lara-asp/commit/650daf73d87d919a36a2f16e303d4c354accd1e7))
* **graphql/`@sortBy`:** `HasMany` support. ([30694ee](https://github.com/LastDragon-ru/lara-asp/commit/30694ee7aad611cf5795410c9459676347bd3cf7))
* **graphql/`@sortBy`:** `HasManyThrough` support. ([745a76c](https://github.com/LastDragon-ru/lara-asp/commit/745a76c900705cf11fe42a215fef3c0092e507a4))
* **graphql/`@sortBy`:** `MorphMany` support. ([5a10f3f](https://github.com/LastDragon-ru/lara-asp/commit/5a10f3fa767de337adb50b252214bf60ed8a6d1a))
* **graphql/`@sortBy`:** Eloquent Builder will sort sub queries to be more consistent. ([915335a](https://github.com/LastDragon-ru/lara-asp/commit/915335a74fce944d093a962af04ba820104e0c7c)) ☣
* **graphql/SchemaPrinter:** Added `Settings::isAlwaysMultilineArguments()` that allow print arguments multi-line always. ([30ffc5f](https://github.com/LastDragon-ru/lara-asp/commit/30ffc5f03db22e59dc4ebcc2ea03d3fc8d345901)) ☣
* **graphql:** Removed code related to `Resolver` concept because it was never (and will not be) used in real life. ([3564e4c](https://github.com/LastDragon-ru/lara-asp/commit/3564e4c0a0acf5c4f65358446ee9834043b4e02a)) ☣


### Bug Fixes

* **migrator:** Commands will be compatible with Laravel ^9.6 ([bf759bd](https://github.com/LastDragon-ru/lara-asp/commit/bf759bd0f5270a29cab68d1a792d287f4af8e4b5))
* **queue:** Return type of `WithInitialization::initialized()` will be `static` instead of `self`. ([f0a952f](https://github.com/LastDragon-ru/lara-asp/commit/f0a952fa1263cf1cb78527db6bbd3062559d0035)) ☣


## [0.13.0](https://github.com/LastDragon-ru/lara-asp/compare/0.12.0...0.13.0) (2022-03-13)

| ☣    | Breaking changes |
|:-----:|:-----------------|

### Features

* **graphql/SchemaPrinter:** `DirectiveResolver::getDefinitions()` will also return standard directives (to be consistent with types). ([633571b](https://github.com/LastDragon-ru/lara-asp/commit/633571be3c932e6f2392ae9d45cbeecf835eac88)) ☣
* **graphql/SchemaPrinter:** `GraphQLExpectedSchema` can define own `Settings`. ([d6bae80](https://github.com/LastDragon-ru/lara-asp/commit/d6bae80fae05a17cb6a2b3c0d359af097ac87065))
* **graphql/SchemaPrinter:** `IntrospectionPrinter` will return only introspection types as unused. ([0601bb8](https://github.com/LastDragon-ru/lara-asp/commit/0601bb8c5dfd867eeb7726343afcd8694608a644))
* **graphql/SchemaPrinter:** `PrintedSchema::getUnusedTypes()` will also return standard types. ([3402e22](https://github.com/LastDragon-ru/lara-asp/commit/3402e229018ee0ee69df47d342ebb4358f8673f4))
* **graphql/SchemaPrinter:** `SchemaPrinter` will use `DirectiveFilter` for standard directives too. ([e33f957](https://github.com/LastDragon-ru/lara-asp/commit/e33f9572b92406b2790eb4061d677dfc3aac408f)) ☣
* **graphql/SchemaPrinter:** Added `PrintedSchema` contract. ([2604f35](https://github.com/LastDragon-ru/lara-asp/commit/2604f35087dde8f6abb2841455a2e04f27898ff0))
* **graphql/SchemaPrinter:** Added `PrintedSchema::getUnusedDirectives()` that will return all unused directives. ([d1a8130](https://github.com/LastDragon-ru/lara-asp/commit/d1a8130e442f9e5c5ffba98ece4dde6d20378753))
* **graphql/SchemaPrinter:** Added `Settings::getDirectiveDefinitionFilter()` that allow exclude Directive Definitions. ([6583742](https://github.com/LastDragon-ru/lara-asp/commit/6583742e17d1554c3a640ef705fd042c1e9b92c8)) ☣
* **graphql/SchemaPrinter:** Added `Settings::getTypeDefinitionFilter()` that allows filter out type definitions. ([504a252](https://github.com/LastDragon-ru/lara-asp/commit/504a252c7f5ba7913151eae1323f169fd1ed6295)) ☣
* **graphql:** `GraphQLAssertions::assertGraphQLSchemaEquals()` update to allow `PrintedSchema` and `Schema` as `$expected`. ([bbff36b](https://github.com/LastDragon-ru/lara-asp/commit/bbff36b24ef9b63b76211d5a3e5bfd0e0811a82e)) ☣


### Bug Fixes

* **graphql/`@sortBy`:** `_` will work with [`@paginate`](https://lighthouse-php.com/5/api-reference/directives.html#paginate). ([ca43d21](https://github.com/LastDragon-ru/lara-asp/commit/ca43d211bf51df0b31f828486e7243d6479c286c)), closes [#12](https://github.com/LastDragon-ru/lara-asp/issues/12) ☣
* **graphql/SchemaPrinter:** `GraphQLAssertions::assertGraphQLSchemaEquals()` will not interpret empty used/unused types/directives lists as "skip assert" (assertion will be skipped only if `null`). ([b722c1c](https://github.com/LastDragon-ru/lara-asp/commit/b722c1cafb7d731f67293ebed0723b83634c0031)) ☣
* **graphql/SchemaPrinter:** `GraphQLAssertions::assertGraphQLSchemaEquals()` will not print expected schema when not necessary. ([1cffe6a](https://github.com/LastDragon-ru/lara-asp/commit/1cffe6ad88bce7ef7c5b19b1fd699f61c92a7e4a))
* **testing:** registration of `StrictAssertEquals`. ([d6e7b50](https://github.com/LastDragon-ru/lara-asp/commit/d6e7b50cab9a50dc77ecbcd5a7124fa70a782133))


### Performance Improvements

* **graphql/SchemaPrinter:** `DirectiveResolver` will not load all lighthouse's directives (because they are not needed + it is very slow) and will cache definitions/instances. ([b61a248](https://github.com/LastDragon-ru/lara-asp/commit/b61a248a4f9890d3a818fccee1313caddf0073d3)) ☣


### Code Refactoring

* **graphql:** Minimal version of "nuwave/lighthouse" set to `^5.8.0`. ([25816cc](https://github.com/LastDragon-ru/lara-asp/commit/25816ccf2bd7972f9bd254a30e1d839c04a16985)) ☣


## [0.12.0](https://github.com/LastDragon-ru/lara-asp/compare/0.11.0...0.12.0) (2022-02-27)

| ☣    | Breaking changes |
|:-----:|:-----------------|


### Features

* Laravel 9 support. (#10; [c12b021](https://github.com/LastDragon-ru/lara-asp/commit/c12b0214483bac52ea44e43bc9193e3904ee253e))
* **migrator:** Removed `DirectorySeeder` and `RootSeeder`. ([1be3a4e](https://github.com/LastDragon-ru/lara-asp/commit/1be3a4e3f20fbc4af53a8665ea9677a9f4e2b9c8)) ☣
* **queue, testing:** `CronableRegistrator` will add disabled `Cronable` into `Schedule` it is needed to be able to test registration. ([cf050e2](https://github.com/LastDragon-ru/lara-asp/commit/cf050e26e093451b2aa41388aa14cfb2270e8817)) ☣
* **spa:** `Request::validated()` updated to be compatible with Laravel 9 and support `$key` and `$default`. ([3eb876f](https://github.com/LastDragon-ru/lara-asp/commit/3eb876f2f70808e3a0561e023c3acff8a15dcefb))


### Bug Fixes

* **graphql:** "LogicException : Override for `Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider` already defined." while testing schemas. ([0623c36](https://github.com/LastDragon-ru/lara-asp/commit/0623c361791f588bc974f3a6c3e1f577fe8abdcf))
* **graphql/SchemaPrinter:** Fixed missing LF in directive locations when the definition is multiline. ([e4d69bf](https://github.com/LastDragon-ru/lara-asp/commit/e4d69bf1b28f58c4a46a46063a1524fcc26c884e))
* **graphql/SchemaPrinter:** Printer will parse all definitions from `Directive:definition()` to avoid "DefinitionException : Lighthouse failed while trying to load a type XXX" error. ([5c20332](https://github.com/LastDragon-ru/lara-asp/commit/5c20332d05223a8611c3cb0e5e9647d693b02453))
* **queue:** `ConsoleKernelWithSchedule` will not use `booted()` because there are no reasons to use it here (also, in some cases the previous approach may lead to `Cronable` was registered twice). ([64482a6](https://github.com/LastDragon-ru/lara-asp/commit/64482a6713b1bfba3e530d43a6ca8e8cccf40469))
* **queue:** `ProviderWithSchedule` will use `afterResolving()` callback to register `Cronable`. ([bf84e0d](https://github.com/LastDragon-ru/lara-asp/commit/bf84e0db8af4b0a66ec786d6311cd459613102b5)) ☣


## [0.11.0](https://github.com/LastDragon-ru/lara-asp/compare/0.10.0...0.11.0) (2022-02-05)

| ☣    | Breaking changes |
|:-----:|:-----------------|
| 🔥    | Something cool   |

### Features

* **core:** Added `Subject::getObservers()`. ([eb59a63](https://github.com/LastDragon-ru/lara-asp/commit/eb59a6345102c128f5e6a337e2301c4879d86519))
* **eloquent:** Added `ModelHelper::isSoftDeletable()` helper. ([b254288](https://github.com/LastDragon-ru/lara-asp/commit/b25428851d28f665a954018c3faa8530ad07c5d1))
* **eloquent:** Result of `ModelHelper::isRelation()` will be cached (cache can be reset by `ModelHelper::resetRelationsCache()`) ([a63f272](https://github.com/LastDragon-ru/lara-asp/commit/a63f2720a5151d047015ca9d6da210dfe1befecf))
* **graphql:** Added `GraphQLExpectedSchema` that can be used with `assertGraphQLSchemaEquals()` to check used/unused types and directives. ([72fd9a9](https://github.com/LastDragon-ru/lara-asp/commit/72fd9a9b821af77f924f18240e626a525098c30d))
* **graphql:** 🔥 Awesome `SchemaPrinter` with directives, filtering, advanced formatting, and more. The `assertGraphQLSchemaEquals()` also updated to use it. ([f9e0b35](https://github.com/LastDragon-ru/lara-asp/commit/f9e0b35e6da765fe315fc3c7109aa6e6fa75bcdc))  ☣
* **graphql:** Minimal version of "nuwave/lighthouse" set to "^5.6.1" (required to print repeatable directives). ([e694b7d](https://github.com/LastDragon-ru/lara-asp/commit/e694b7d2693933e8a072628a3e2ea04b11175d7d)) ☣


### Bug Fixes

* **formatter:** `Formatter::forLocale()`/`Formatter::forTimezone()` will not lose `timezone`/`locale`. ([940ed7b](https://github.com/LastDragon-ru/lara-asp/commit/940ed7babfa6a9c598fbfd1c7eef3e22e66492ab))
* **testing:** `Override` will check usages in `assertPostConditions()` instead of `beforeApplicationDestroyed()` ("Lock wait timeout exceeded" fix). ([95649d9](https://github.com/LastDragon-ru/lara-asp/commit/95649d9af0a8ee01314db3127a6bb478a265deab))


### Code Refactoring

* **core:** `Subject` methods will return `self` instead of `void`. ([7d80539](https://github.com/LastDragon-ru/lara-asp/commit/7d80539387818c838bc28c579bd2a79cc2731bdb)) ☣
* **core:** Class `Subject` converted into interface, all methods moved into new class `Dispatcher`. ([e7bef00](https://github.com/LastDragon-ru/lara-asp/commit/e7bef003e1fb32b30dc0213849cde94e148dd37a)) ☣


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
