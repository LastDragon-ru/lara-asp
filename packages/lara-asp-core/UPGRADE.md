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

# Upgrade from v9

* [ ] Deprecated `💀\LastDragon_ru\LaraASP\Core\Utils\Path` removed, please use [`💀FilePath`][code-links/5dccbbbbfd89f5f6]/[`💀DirectoryPath`][code-links/6a882555d8c99237] instead.

* [ ] Deprecated `💀\LastDragon_ru\LaraASP\Core\Path\Path::isMatch()`, the `preg_match()` can be used directly.

* [ ] The Path component is deprecated, please migrate to [`lastdragon-ru/path`](../path/README.md) package 🤝
  * [`💀DirectoryPath`][code-links/6a882555d8c99237]
  * [`💀FilePath`][code-links/5dccbbbbfd89f5f6]
  * [`💀Path`][code-links/277f4a31cdbb54b4]

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

* [ ] [`WithRoutes::bootRoutes()`][code-links/141085a29c14a778] requires settings.

* [ ] Use [`PackageProvider`][code-links/b1bdaf40c86b0742] instead of [`💀Provider`][code-links/8b4dc3d615948332].

# Upgrade from v5

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: preprocess/2e85dad2b0618274)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: preprocess/2e85dad2b0618274)

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/b1bdaf40c86b0742]: src/PackageProvider.php
    "\LastDragon_ru\LaraASP\Core\PackageProvider"

[code-links/6a882555d8c99237]: src/Path/DirectoryPath.php
    "\LastDragon_ru\LaraASP\Core\Path\DirectoryPath"

[code-links/5dccbbbbfd89f5f6]: src/Path/FilePath.php
    "\LastDragon_ru\LaraASP\Core\Path\FilePath"

[code-links/277f4a31cdbb54b4]: src/Path/Path.php
    "\LastDragon_ru\LaraASP\Core\Path\Path"

[code-links/8b4dc3d615948332]: src/Provider.php
    "\LastDragon_ru\LaraASP\Core\Provider"

[code-links/141085a29c14a778]: src/Provider/WithRoutes.php#L18-L45
    "\LastDragon_ru\LaraASP\Core\Provider\WithRoutes::bootRoutes()"

[//]: # (end: code-links)
