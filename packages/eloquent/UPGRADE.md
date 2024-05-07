# Upgrade Guide

[include:file]: ../../docs/Shared/Upgrade.md
[//]: # (start: 5af9759519da3fa710fb21785e61682fda687a6ebdfb6f0dde4ed03162cb031d)
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

[//]: # (end: 5af9759519da3fa710fb21785e61682fda687a6ebdfb6f0dde4ed03162cb031d)

# Upgrade from v6

[include:file]: ../../docs/Shared/Upgrade/FromV6.md
[//]: # (start: 8dae6cc48a78a268dcc7b747e512f85b410c9a9392ffac0734f4b17d390f1883)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Direct usages of `Container::getInstances()` were replaced by explicit constructor parameters. You may need to update your code accordingly (#151).

[//]: # (end: 8dae6cc48a78a268dcc7b747e512f85b410c9a9392ffac0734f4b17d390f1883)

# Upgrade from v5

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: 599c87007f162e34f4fd0c7874d4fcf8676e5d8c761d27a9456b284c7d1d12f2)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: 599c87007f162e34f4fd0c7874d4fcf8676e5d8c761d27a9456b284c7d1d12f2)
