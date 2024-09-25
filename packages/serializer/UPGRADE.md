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

[include:file]: ../../docs/Shared/Upgrade/FromV6.md
[//]: # (start: preprocess/9679e76379216855)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] PHP 8.1 is not supported anymore. Migrate to the newer version.

* [ ] Direct usages of `Container::getInstances()` were replaced by explicit constructor parameters. You may need to update your code accordingly (#151).

[//]: # (end: preprocess/9679e76379216855)

# Upgrade from v5

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: preprocess/2e85dad2b0618274)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: preprocess/2e85dad2b0618274)
