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

# Upgrade from v9

* [ ] `💀\LastDragon_ru\LaraASP\Serializer\Normalizers\UnitEnumNormalizer` replaced by [`EnumNormalizer`][code-links/c063e3779af28ba1].

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

* [ ] Package config now uses objects instead of an array, it is recommended to migrate to the new format. 🤝

* [ ] Use [`PackageProvider`][code-links/f67757c3076438c4] instead of [`💀Provider`][code-links/675ff07d439efcf2].

# Upgrade from v5

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: preprocess/2e85dad2b0618274)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: preprocess/2e85dad2b0618274)

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/c063e3779af28ba1]: src/Normalizers/EnumNormalizer.php
    "\LastDragon_ru\LaraASP\Serializer\Normalizers\EnumNormalizer"

[code-links/f67757c3076438c4]: src/PackageProvider.php
    "\LastDragon_ru\LaraASP\Serializer\PackageProvider"

[code-links/675ff07d439efcf2]: src/Provider.php
    "\LastDragon_ru\LaraASP\Serializer\Provider"

[//]: # (end: code-links)
