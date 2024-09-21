# Upgrade Guide

[include:file]: ../../docs/Shared/Upgrade.md
[//]: # (start: 8e89e65b3785cb5b41f28a4f3c5b7e0db0110d8047852d71cd99b2cdffd8f57c)
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

[//]: # (end: 8e89e65b3785cb5b41f28a4f3c5b7e0db0110d8047852d71cd99b2cdffd8f57c)

# Upgrade from v6

[include:file]: ../../docs/Shared/Upgrade/FromV6.md
[//]: # (start: 470dd21d18d5886f1873b1247130ac8173ed99258e41418c6bd32162325d628b)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] PHP 8.1 is not supported anymore. Migrate to the newer version.

* [ ] Direct usages of `Container::getInstances()` were replaced by explicit constructor parameters. You may need to update your code accordingly (#151).

[//]: # (end: 470dd21d18d5886f1873b1247130ac8173ed99258e41418c6bd32162325d628b)

* [ ] Following traits required `app()` method to get access to the Container (#151)
  * [`ScheduleAssertions`][code-links/5bf3a6e818e8ec48]
  * [`Override`][code-links/a5e57679c3a947a6]
  * [`WithQueryLog`][code-links/e6637d2e31bd9516]
  * [`💀RefreshDatabaseIfEmpty`][code-links/1e9b6004b06c7c68]
  * [`WithTranslations`][code-links/733eb8fbc4b211a5]

  ```php
  protected function app(): Application {
      return $this->app;
  }
  ```

* [ ] [`ScheduleAssertions`][code-links/5bf3a6e818e8ec48] methods became non-static (#151).

# Upgrade from v5

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: 374d3c27b4b7982387512d35047d26f2bce3dd6c7b06bc14e53fdcd74bad8102)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: 374d3c27b4b7982387512d35047d26f2bce3dd6c7b06bc14e53fdcd74bad8102)

* [ ] Replace `CronableAssertions::assertCronableRegistered()` to `ScheduleAssertions::assertScheduled()`.

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/5bf3a6e818e8ec48]: src/Assertions/Application/ScheduleAssertions.php "\LastDragon_ru\LaraASP\Testing\Assertions\Application\ScheduleAssertions"

[code-links/a5e57679c3a947a6]: src/Concerns/Override.php "\LastDragon_ru\LaraASP\Testing\Concerns\Override"

[code-links/e6637d2e31bd9516]: src/Database/QueryLog/WithQueryLog.php "\LastDragon_ru\LaraASP\Testing\Database\QueryLog\WithQueryLog"

[code-links/1e9b6004b06c7c68]: src/Database/RefreshDatabaseIfEmpty.php "\LastDragon_ru\LaraASP\Testing\Database\RefreshDatabaseIfEmpty"

[code-links/733eb8fbc4b211a5]: src/Utils/WithTranslations.php "\LastDragon_ru\LaraASP\Testing\Utils\WithTranslations"

[//]: # (end: code-links)
