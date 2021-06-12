## [0.6.0](https://github.com/LastDragon-ru/lara-asp/compare/0.5.0...0.6.0) (2021-06-12)


### Features

* **graphql:** `[@sort](https://github.com/sort)By`: [HasOneThrough](https://laravel.com/docs/5.8/eloquent-relationships#has-one-through) support 😜 ([558198b](https://github.com/LastDragon-ru/lara-asp/commit/558198b5672757cbb039b6b23211af30e5823b62))
* **testing:** Added `WithTempFile` helper. ([93a24be](https://github.com/LastDragon-ru/lara-asp/commit/93a24be20fc42e64f2962a5077d37d0ea3f0f801))
* **testing:** Signature of `DataProvider::getData()` changed to `DataProvider::getData(bool $raw = false)` to allow nesting DataProviders. ([34f1ff7](https://github.com/LastDragon-ru/lara-asp/commit/34f1ff7b8e4667c93c9fa4e07f167e8061394331))


### Bug Fixes

* **dev:** Added removing special characters from hostname in Vagrantfile. ([1eed300](https://github.com/LastDragon-ru/lara-asp/commit/1eed3003ad1d2a5c8ebc56f60f89c71ab68d70c7))
* **graphql:** `[@search](https://github.com/search)By` will not advise "contact to developer" for unknown types. ([d9ff32b](https://github.com/LastDragon-ru/lara-asp/commit/d9ff32b6151065b9871d6b46cbadf776b8080590))
* **graphql:** `[@sort](https://github.com/sort)By` will not skip "unknown" types and will not convert them into Scalars. ([b5aaccb](https://github.com/LastDragon-ru/lara-asp/commit/b5aaccb981caca25b62fbe08a3b8139abcc74840))
* **testing:** arguments types for `TestResponseMixin::assertJsonMatchesSchema()`. ([46674f0](https://github.com/LastDragon-ru/lara-asp/commit/46674f037958a6a4e66de4e2d69b60d621fc1e58))

## [0.5.0](https://github.com/LastDragon-ru/lara-asp/compare/0.4.0...0.5.0) (2021-05-16)


### Features

* **graphql:** Added two highly powerful `@searchBy` and `@sortBy`  directives for [lighthouse-php](https://lighthouse-php.com/). The `@searchBy` directive provides basic conditions like `=`, `>`, `<`, etc, relations, `not (<condition>)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The `@sortBy` is not only about standard sorting by columns but also allows use relations. 😎
