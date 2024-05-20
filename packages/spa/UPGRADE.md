# Upgrade Guide

[include:file]: ../../docs/Shared/Upgrade.md
[//]: # (start: 3fcf292b870e49aabe885c416dfaf6c6c66d4571e87d3d3975d9cfe34ea6c9fe)
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

[//]: # (end: 3fcf292b870e49aabe885c416dfaf6c6c66d4571e87d3d3975d9cfe34ea6c9fe)

# Upgrade from v6

[include:file]: ../../docs/Shared/Upgrade/FromV6.md
[//]: # (start: b0b74ef74f156294a37f3ec42299e221e5e693f3b42297f5cfa79cab99b1df7e)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Direct usages of `Container::getInstances()` were replaced by explicit constructor parameters. You may need to update your code accordingly (#151).

[//]: # (end: b0b74ef74f156294a37f3ec42299e221e5e693f3b42297f5cfa79cab99b1df7e)

# Upgrade from v5

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: 6367638a165291d78965aaeae9ab03b304b0a420eb3f5ad9af0424296cc609ea)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: 6367638a165291d78965aaeae9ab03b304b0a420eb3f5ad9af0424296cc609ea)
