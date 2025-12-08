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

* [ ] Metadata renamed to cast. The [`Cast`][code-links/6a213cdb7ed49c73] should be used instead of
  * `💀\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver`
  * `💀\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataSerializer`

* [ ] The [`Task`][code-links/405a2082bc21eb5b] split into two new classes that should be used instead
  * [`FileTask`][code-links/42d900d10c3c5f5c]
  * [`HookTask`][code-links/b3d5664fcbd8bbf1]

* [ ] The [`Processor`][code-links/e7daa686f09d9cc3] has fewer methods to simplify the API.

* [ ] Deprecated `💀\LastDragon_ru\LaraASP\Documentator\Utils\Markdown` removed, please use [`Document`][code-links/ab9a95ccf7b21703] instead.

* [ ] Directories concept was removed to simplify API.

* [ ] Completely reworked [`Resolver`][code-links/73efdc4c56c9ba17] simplify API.

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

* [ ] `💀\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task::__invoke()` should yield a `💀\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency` instead of file.

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

[code-links/ab9a95ccf7b21703]: src/Markdown/Contracts/Document.php
    "\LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document"

[code-links/bddbc83c8cbd0c67]: src/PackageProvider.php
    "\LastDragon_ru\LaraASP\Documentator\PackageProvider"

[code-links/6a213cdb7ed49c73]: src/Processor/Contracts/Cast.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast"

[code-links/73efdc4c56c9ba17]: src/Processor/Contracts/Resolver.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver"

[code-links/405a2082bc21eb5b]: src/Processor/Contracts/Task.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task"

[code-links/42d900d10c3c5f5c]: src/Processor/Contracts/Tasks/FileTask.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\FileTask"

[code-links/b3d5664fcbd8bbf1]: src/Processor/Contracts/Tasks/HookTask.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks\HookTask"

[code-links/e7daa686f09d9cc3]: src/Processor/Processor.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Processor"

[code-links/6312f45bb1f04802]: src/Processor/Tasks/Preprocess/Contracts/Instruction.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction"

[code-links/ecd75d864090a13d]: src/Processor/Tasks/Preprocess/Contracts/Parameters.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters"

[code-links/f9077a28b352f84b]: src/Processor/Tasks/Preprocess/Instructions/IncludeExample/Contracts/Runner.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Contracts\Runner"

[code-links/a76f14008cba70b9]: src/Provider.php
    "\LastDragon_ru\LaraASP\Documentator\Provider"

[//]: # (end: code-links)
