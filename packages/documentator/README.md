# (Laravel) Documentator

This package provides various utilities for documentation generation such as Markdown Preprocessor, Requirements Dumper and more.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: 0f999169cbabc32d4f47c79c31d74f8b4066c685962719bae5df3c63a08ea382)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 5.0.0-beta.1`   |
|  | `^8.1` |   `6.4.2 ⋯ 5.0.0-beta.1`   |
|  Laravel  | `^11.0.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.34.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.0.0` |   `6.1.0 ⋯ 5.0.0-beta.1`   |
|  | `^9.21.0` |   `5.6.0 ⋯ 5.0.0-beta.1`   |

[//]: # (end: 0f999169cbabc32d4f47c79c31d74f8b4066c685962719bae5df3c63a08ea382)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "documentator"}})
[//]: # (start: ec326af8e6529977dcb44b67335b70be8b3aefaff2344a491c075d1bbeae58bb)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-documentator
```

[//]: # (end: ec326af8e6529977dcb44b67335b70be8b3aefaff2344a491c075d1bbeae58bb)

# Commands

[include:document-list]: ./docs/Commands
[//]: # (start: afb4e2440d52a76ba0a75c90795760817a659138d71c9a463a0e417d9abb178a)
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

[//]: # (end: afb4e2440d52a76ba0a75c90795760817a659138d71c9a463a0e417d9abb178a)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: fc88f84f187016cb8144e9a024844024492f0c3a5a6f8d128bf69a5814cc8cc5)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: fc88f84f187016cb8144e9a024844024492f0c3a5a6f8d128bf69a5814cc8cc5)
