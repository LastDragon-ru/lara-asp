# (Laravel) Documentator

This package provides various utilities for documentation generation such as Markdown Preprocessor, Requirements Dumper and more.

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 5.0.0-beta.1`   |
|  | `^8.1` |   `HEAD ⋯ 5.0.0-beta.1`   |
|  Laravel  | `^11.0.0` |  `HEAD`   |
|  | `^10.34.0` |  `HEAD`   |
|  | `^10.0.0` |   `6.1.0 ⋯ 5.0.0-beta.1`   |
|  | `^9.21.0` |   `5.6.0 ⋯ 5.0.0-beta.1`   |

[//]: # (end: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)

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
[//]: # (start: 3183e17484393f086cbec523de70a97446749151d781d55ef8f480075e5c75b9)
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

[//]: # (end: 3183e17484393f086cbec523de70a97446749151d781d55ef8f480075e5c75b9)

[include:file]: ../../docs/Shared/Upgrading.md
[//]: # (start: e9139abedb89f69284102c9112b548fd7add07cf196259916ea4f1c98977223b)
[//]: # (warning: Generated automatically. Do not edit.)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[//]: # (end: e9139abedb89f69284102c9112b548fd7add07cf196259916ea4f1c98977223b)

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: 057ec3a599c54447e95d6dd2e9f0f6a6621d9eb75446a5e5e471ba9b2f414b89)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 057ec3a599c54447e95d6dd2e9f0f6a6621d9eb75446a5e5e471ba9b2f414b89)
