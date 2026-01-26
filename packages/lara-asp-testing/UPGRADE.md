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

Please also see [changelog](https://github.com/LastDragon-ru/php-packages/releases) to find all changes.

## Legend

| 🤝 | Backward-compatible change. Please note that despite you can ignore it now, but it will be mandatory in the future. |
|:--:|:--------------------------------------------------------------------------------------------------------------------|

[//]: # (end: preprocess/aa9fc458898c7c1c)

# Upgrade from v10

* [ ] Class [`💀WithTempFile`][code-links/1e768fc484194d84] deprecated, the [`TempFile`][code-links/2ed0bfaade389715] should be used instead 🤝
* [ ] Class [`💀WithTempDirectory`][code-links/8ccb455c0defa9b1] deprecated, the [`TempDirectory`][code-links/988d0b3180c21a3f] should be used instead 🤝
* [ ] Trait [`💀FileSystemAssertions`][code-links/f05d4c94687fa6e8] deprecated, the [`Assertions`][code-links/d3222cbf284d2c91] should be used instead 🤝
* [ ] XML Assertions are deprecated, the [`lastdragon-ru/phpunit-xml`](../phpunit-xml/README.md) should be used instead 🤝

# Upgrade from v9

* [ ] Extension [`💀\LastDragon_ru\LaraASP\Testing\Requirements\PhpUnit\Extension`][code-links/26cc04e820d354b1] deprecated, please use [`\LastDragon_ru\PhpUnit\Extensions\Requirements\Extension`][code-links/7e51b51dd292df33] instead 🤝
*
* [ ] Trait [`💀StrictAssertEquals`][code-links/2187ed1b4d4e6c14] deprecated, please use [`\LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare\Extension`][code-links/50cb69b702caae36] instead 🤝

* [ ] Trait [`💀WithTestData`][code-links/c1153ff984856b36] and [`💀\LastDragon_ru\LaraASP\Testing\Utils\TestData`][code-links/84706d7f00aadc5e] deprecated, please use [`\LastDragon_ru\PhpUnit\Utils\TestData`][code-links/ded4ad00d1ea1842] instead 🤝

# Upgrade from v7

[include:file]: ../../docs/Shared/Upgrade/FromV7.md
[//]: # (start: preprocess/c45228918cc92f69)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] PHP 8.2 is not supported anymore. Migrate to the newer version.
* [ ] Laravel v10 is not supported anymore. Migrate to the newer version.

[//]: # (end: preprocess/c45228918cc92f69)

# Upgrade from v6

[include:file]: ../../docs/Shared/Upgrade/FromV6.md
[//]: # (start: preprocess/9679e76379216855)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] PHP 8.1 is not supported anymore. Migrate to the newer version.

* [ ] Direct usages of `Container::getInstances()` were replaced by explicit constructor parameters. You may need to update your code accordingly (#151).

[//]: # (end: preprocess/9679e76379216855)

* [ ] Following traits required `app()` method to get access to the Container (#151)
  * [`ScheduleAssertions`][code-links/5bf3a6e818e8ec48]
  * [`Override`][code-links/a5e57679c3a947a6]
  * [`WithQueryLog`][code-links/e6637d2e31bd9516]
  * `💀\LastDragon_ru\LaraASP\Testing\Database\RefreshDatabaseIfEmpty`
  * [`WithTranslations`][code-links/733eb8fbc4b211a5]

  ```php
  protected function app(): Application {
      return $this->app;
  }
  ```

* [ ] [`ScheduleAssertions`][code-links/5bf3a6e818e8ec48] methods became non-static and signature changes (#151).

# Upgrade from v5

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: preprocess/2e85dad2b0618274)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: preprocess/2e85dad2b0618274)

* [ ] Replace `CronableAssertions::assertCronableRegistered()` to `ScheduleAssertions::assertScheduled()`.

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/5bf3a6e818e8ec48]: src/Assertions/Application/ScheduleAssertions.php
    "\LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleAssertions"

[code-links/f05d4c94687fa6e8]: src/Assertions/FileSystemAssertions.php
    "\LastDragon_ru\LaraASP\Testing\Assertions\FileSystemAssertions"

[code-links/a5e57679c3a947a6]: src/Concerns/Override.php
    "\LastDragon_ru\LaraASP\Testing\Concerns\Override"

[code-links/2187ed1b4d4e6c14]: src/Concerns/StrictAssertEquals.php
    "\LastDragon_ru\LaraASP\Testing\Concerns\StrictAssertEquals"

[code-links/e6637d2e31bd9516]: src/Database/QueryLog/WithQueryLog.php
    "\LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog"

[code-links/26cc04e820d354b1]: src/Requirements/PhpUnit/Extension.php
    "\LastDragon_ru\LaraASP\Testing\Requirements\PhpUnit\Extension"

[code-links/84706d7f00aadc5e]: src/Utils/TestData.php
    "\LastDragon_ru\LaraASP\Testing\Utils\TestData"

[code-links/8ccb455c0defa9b1]: src/Utils/WithTempDirectory.php
    "\LastDragon_ru\LaraASP\Testing\Utils\WithTempDirectory"

[code-links/1e768fc484194d84]: src/Utils/WithTempFile.php
    "\LastDragon_ru\LaraASP\Testing\Utils\WithTempFile"

[code-links/c1153ff984856b36]: src/Utils/WithTestData.php
    "\LastDragon_ru\LaraASP\Testing\Utils\WithTestData"

[code-links/733eb8fbc4b211a5]: src/Utils/WithTranslations.php
    "\LastDragon_ru\LaraASP\Testing\Utils\WithTranslations"

[code-links/7e51b51dd292df33]: ../phpunit-extensions/src/Extensions/Requirements/Extension.php
    "\LastDragon_ru\PhpUnit\Extensions\Requirements\Extension"

[code-links/50cb69b702caae36]: ../phpunit-extensions/src/Extensions/StrictScalarCompare/Extension.php
    "\LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare\Extension"

[code-links/d3222cbf284d2c91]: ../phpunit-extensions/src/Filesystem/Assertions.php
    "\LastDragon_ru\PhpUnit\Filesystem\Assertions"

[code-links/988d0b3180c21a3f]: ../phpunit-extensions/src/Utils/TempDirectory.php
    "\LastDragon_ru\PhpUnit\Utils\TempDirectory"

[code-links/2ed0bfaade389715]: ../phpunit-extensions/src/Utils/TempFile.php
    "\LastDragon_ru\PhpUnit\Utils\TempFile"

[code-links/ded4ad00d1ea1842]: ../phpunit-extensions/src/Utils/TestData.php
    "\LastDragon_ru\PhpUnit\Utils\TestData"

[//]: # (end: code-links)
