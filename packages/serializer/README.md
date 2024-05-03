# (Laravel) Symfony Serializer

This package provides a customizable wrapper around the [Symfony Serializer Component](https://symfony.com/doc/current/components/serializer.html) to use it inside Laravel application.

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 5.0.0-beta.0`   |
|  | `^8.1` |   `HEAD ⋯ 5.0.0-beta.0`   |
|  Laravel  | `^11.0.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.34.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.0.0` |   `6.1.0 ⋯ 5.0.0-beta.0`   |
|  | `^9.21.0` |   `5.6.0 ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |  `5.0.0-beta.0`   |

[//]: # (end: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "serializer"}})
[//]: # (start: d8b5372aebffede51da53eb1cdc31143e965ae14f00992219dae456a565cda4a)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-serializer
```

[//]: # (end: d8b5372aebffede51da53eb1cdc31143e965ae14f00992219dae456a565cda4a)

# Usage

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: 25c8cf0ee2862aeda3cd8ff6bf8d2d3592fee1c00042550be5ee7686ead4cc44)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration

namespace LastDragon_ru\LaraASP\Serializer\Docs\Examples\Usage;

use DateTimeInterface;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;

class User implements Serializable {
    public function __construct(
        public int $id,
        public string $name,
        public DateTimeInterface $created,
    ) {
        // empty
    }
}

$user         = new User(1, 'User', Date::parse('2023-08-27T08:30:44.473+00:00'));
$serializer   = app()->make(Serializer::class);
$serialized   = $serializer->serialize($user);
$deserialized = $serializer->deserialize(User::class, $serialized);

Example::dump($serialized);
Example::dump($deserialized);
```

The `$serialized` is:

```plain
"{"id":1,"name":"User","created":"2023-08-27T08:30:44.473+00:00"}"
```

The `$deserialized` is:

```plain
LastDragon_ru\LaraASP\Serializer\Docs\Examples\Usage\User {
  +id: 1
  +name: "User"
  +created: Illuminate\Support\Carbon {
    +"date": "2023-08-27 08:30:44.473000"
    +"timezone_type": 1
    +"timezone": "+00:00"
  }
}
```

[//]: # (end: 25c8cf0ee2862aeda3cd8ff6bf8d2d3592fee1c00042550be5ee7686ead4cc44)

# Extending

Out of the box, the package supports only the following objects (see [`Factory`](./src/Factory.php) for more details):

* Any object that implement [`Serializable`](./src/Contracts/Serializable.php) (see [`SerializableNormalizer`](./src/Normalizers/SerializableNormalizer.php))
* Any object that implement `\DateTimeInterface` (see [`DateTimeNormalizer`](./src/Normalizers/DateTimeNormalizer.php))
* `\DateTimeZone`
* `\DateInterval`
* PHP Enums

Publish the config and add normalizers/denormalizers if you need more:

```shell
php artisan vendor:publish --provider=LastDragon_ru\\LaraASP\\Serializer\\Provider --tag=config
```

# Eloquent Cast[^1]

You can use the [`LastDragon_ru\LaraASP\Serializer\Casts\AsSerializable`](./src/Casts/AsSerializable.php) cast class to cast a model string attribute to an object:

[include:example]: ./docs/Examples/AsSerializable.php
[//]: # (start: 17152ed9d4094e5a2bb12c34f6fdb2f223a90f75c9ae440580dded1082fe6dec)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration

namespace LastDragon_ru\LaraASP\Serializer\Docs\Examples\AsSerializable;

use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Serializer\Casts\AsSerializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Override;

use function array_merge;

class UserSettings implements Serializable {
    public function __construct(
        public int $perPage,
        public bool $showSidebar,
    ) {
        // empty
    }
}

/**
 * @property UserSettings|null $settings
 */
class User extends Model {
    /**
     * @return array<array-key, mixed>
     */
    #[Override]
    protected function casts(): array {
        return array_merge(parent::casts(), [
            'settings' => AsSerializable::using(UserSettings::class),
        ]);
    }
}

$user           = new User();
$user->settings = new UserSettings(35, false);

Example::dump($user->settings);
Example::dump($user->getAttributes());
```

The `$user->settings` is:

```plain
LastDragon_ru\LaraASP\Serializer\Docs\Examples\AsSerializable\UserSettings {
  +perPage: 35
  +showSidebar: false
}
```

The `$user->getAttributes()` is:

```plain
[
  "settings" => "{"perPage":35,"showSidebar":false}",
]
```

[//]: # (end: 17152ed9d4094e5a2bb12c34f6fdb2f223a90f75c9ae440580dded1082fe6dec)

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

[^1]: <https://laravel.com/docs/eloquent-mutators>
