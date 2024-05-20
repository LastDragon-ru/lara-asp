# (Laravel) Documentator

This package provides various utilities for documentation generation such as Markdown Preprocessor, Requirements Dumper and more.

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 0c754acbee0a8071717d81a4c18765bb2d605f138e08492b868c0e3f27e481ed)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 5.0.0-beta.1`   |
|  | `^8.1` |   `HEAD ⋯ 5.0.0-beta.1`   |
|  Laravel  | `^11.0.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.34.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.0.0` |   `6.1.0 ⋯ 5.0.0-beta.1`   |
|  | `^9.21.0` |   `5.6.0 ⋯ 5.0.0-beta.1`   |

[//]: # (end: 0c754acbee0a8071717d81a4c18765bb2d605f138e08492b868c0e3f27e481ed)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "documentator"}})
[//]: # (start: d830b5dad8950e88a29e14aa443ca509cfa19889b5c3792b00691760fb8618bb)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-documentator
```

[//]: # (end: d830b5dad8950e88a29e14aa443ca509cfa19889b5c3792b00691760fb8618bb)

# Commands

[include:document-list]: ./docs/Commands
[//]: # (start: bf4572c5a716c01f9df13bb1bfb0e1a6d9c62b3f19f5467f152a12da3f5d92ad)
[//]: # (warning: Generated automatically. Do not edit.)

## `lara-asp-documentator:commands`

Saves help for each command in the `namespace` into a separate file in the `target` directory.

[Read more](<docs/Commands/commands.md>).

## `lara-asp-documentator:preprocess`

Preprocess Markdown files.

[Read more](<docs/Commands/preprocess.md>).

## `lara-asp-documentator:requirements`

Generates a table with the required versions of PHP/Laravel/etc in Markdown format.

[Read more](<docs/Commands/requirements.md>).

[//]: # (end: bf4572c5a716c01f9df13bb1bfb0e1a6d9c62b3f19f5467f152a12da3f5d92ad)

[include:file]: ../../docs/Shared/Upgrading.md
[//]: # (start: 5f4a27dda34e5e151a62fe3459daf4bb3b85705d38810060e71fcadc25669c0f)
[//]: # (warning: Generated automatically. Do not edit.)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[//]: # (end: 5f4a27dda34e5e151a62fe3459daf4bb3b85705d38810060e71fcadc25669c0f)

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: 3f7cfa48046722fb9d277c71e074ff8406787772f90d17405b7554a4464cbfee)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 3f7cfa48046722fb9d277c71e074ff8406787772f90d17405b7554a4464cbfee)
