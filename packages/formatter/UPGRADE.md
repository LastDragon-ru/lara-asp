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

# Upgrade from v6

This version is the deep refactoring of the [`Formatter`][code-links/9fbde97537a14196] class to make it simple and allow adding new formats easily. All built-in formats are now instances of [`Format`][code-links/f729e209367a8080] interface. Also, the config now is the [`Config`][code-links/d45c59bc79a55ae4] instance instead of an array, and locale-specific settings were moved into format itself. Please check the updated documentation for more details.

[include:file]: ../../docs/Shared/Upgrade/FromV6.md
[//]: # (start: preprocess/9679e76379216855)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] PHP 8.1 is not supported anymore. Migrate to the newer version.

* [ ] Direct usages of `Container::getInstances()` were replaced by explicit constructor parameters. You may need to update your code accordingly (#151).

[//]: # (end: preprocess/9679e76379216855)

* [ ] Array-based config is not supported anymore. Please migrate to object-based config.

* [ ] Use [`PackageProvider`][code-links/53319d866f52d561] instead of [`💀Provider`][code-links/2d32d5931e8c93e4].

# Upgrade from v5

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: preprocess/2e85dad2b0618274)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: preprocess/2e85dad2b0618274)

* [ ] If you are passing `\IntlDateFormatter::*` constants as `$format` argument for `Formatter::time()`/`Formatter::date()`/`Formatter::datetime()`, add a new custom format(s) which will refer to `\IntlDateFormatter::*` constant(s).

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/d45c59bc79a55ae4]: src/Config/Config.php
    "\LastDragon_ru\LaraASP\Formatter\Config\Config"

[code-links/f729e209367a8080]: src/Contracts/Format.php
    "\LastDragon_ru\LaraASP\Formatter\Contracts\Format"

[code-links/9fbde97537a14196]: src/Formatter.php
    "\LastDragon_ru\LaraASP\Formatter\Formatter"

[code-links/53319d866f52d561]: src/PackageProvider.php
    "\LastDragon_ru\LaraASP\Formatter\PackageProvider"

[code-links/2d32d5931e8c93e4]: src/Provider.php
    "\LastDragon_ru\LaraASP\Formatter\Provider"

[//]: # (end: code-links)
