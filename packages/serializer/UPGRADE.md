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

# Upgrade from v5

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: 374d3c27b4b7982387512d35047d26f2bce3dd6c7b06bc14e53fdcd74bad8102)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: 374d3c27b4b7982387512d35047d26f2bce3dd6c7b06bc14e53fdcd74bad8102)
