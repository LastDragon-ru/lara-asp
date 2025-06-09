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

* [ ] Migrate to the new contract:
  * [`Instruction`][code-links/6312f45bb1f04802]
  * [`Parameters`][code-links/ecd75d864090a13d].

* [ ] Instruction `include:example` not check/run `<example>.run` file anymore. The [`Runner`][code-links/f9077a28b352f84b] should be used/provided instead.

* [ ] [`Task::__invoke()`][code-links/ac42b74d053a366b] should yield a [`Dependency`][code-links/f4718f92376c3c25] instead of file.

* [ ] `💀\LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileDependencyNotFound` replaced by `💀\LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyNotFound`.

* [ ] Use [`PackageProvider`][code-links/bddbc83c8cbd0c67] instead of [`💀Provider`][code-links/a76f14008cba70b9].

# Upgrade from v5

[include:file]: ../../docs/Shared/Upgrade/FromV5.md
[//]: # (start: preprocess/2e85dad2b0618274)
[//]: # (warning: Generated automatically. Do not edit.)

* [ ] Laravel v9 is not supported anymore. Migrate to the newer version.

[//]: # (end: preprocess/2e85dad2b0618274)

* [ ] Replace `💀\LastDragon_ru\LaraASP\Documentator\Preprocessor\InstructionContract` by `💀\LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction` or `💀\LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction`.

* [ ] Use [`illuminate/process`](https://laravel.com/docs/processes) instead of `💀\LastDragon_ru\LaraASP\Documentator\Utils\Process`.

* [ ] If you are extending built-in instructions, their classes were moved to `LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\<name>\Instruction` namespace.

* [ ] If you are extending built-in templates, they were renamed from `markdown.blade.php` to `default.blade.php`.

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/bddbc83c8cbd0c67]: src/PackageProvider.php
    "\LastDragon_ru\LaraASP\Documentator\PackageProvider"

[code-links/f4718f92376c3c25]: src/Processor/Contracts/Dependency.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency"

[code-links/ac42b74d053a366b]: src/Processor/Contracts/Task.php#L17-L20
    "\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task::__invoke()"

[code-links/6312f45bb1f04802]: src/Processor/Tasks/Preprocess/Contracts/Instruction.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction"

[code-links/ecd75d864090a13d]: src/Processor/Tasks/Preprocess/Contracts/Parameters.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters"

[code-links/f9077a28b352f84b]: src/Processor/Tasks/Preprocess/Instructions/IncludeExample/Contracts/Runner.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Contracts\Runner"

[code-links/a76f14008cba70b9]: src/Provider.php
    "\LastDragon_ru\LaraASP\Documentator\Provider"

[//]: # (end: code-links)
