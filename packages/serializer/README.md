# (Laravel) Symfony Serializer

This package provides a customizable wrapper around the [Symfony Serializer Component](https://symfony.com/doc/current/components/serializer.html) to use it inside Laravel application.

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 0c754acbee0a8071717d81a4c18765bb2d605f138e08492b868c0e3f27e481ed)
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

[//]: # (end: 0c754acbee0a8071717d81a4c18765bb2d605f138e08492b868c0e3f27e481ed)

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
[//]: # (start: adef85def212f87b8f157b40eaa0695da87e1a6f63e1e1ebc9fff474ecad3fee)
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

[//]: # (end: adef85def212f87b8f157b40eaa0695da87e1a6f63e1e1ebc9fff474ecad3fee)

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

# Eloquent Accessor/Mutator[^1]

You can use the [`LastDragon_ru\LaraASP\Serializer\Casts\Serialized`](./src/Casts/Serialized.php) attribute to populate a model attribute to/from an object:

[include:example]: ./docs/Examples/Attribute.php
[//]: # (start: f35a6e7b23df02996a9581e4c34db9192ceb148e1fa7ed361c57fc6a017bd8e9)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration

namespace LastDragon_ru\LaraASP\Serializer\Docs\Examples\Attribute;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Serializer\Casts\Serialized;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class UserSettings implements Serializable {
    public function __construct(
        public int $perPage,
        public bool $showSidebar,
    ) {
        // empty
    }
}

class User extends Model {
    /**
     * @return Attribute<?UserSettings, ?UserSettings>
     */
    protected function settings(): Attribute {
        return app()->make(Serialized::class)->attribute(UserSettings::class);
    }
}

$user           = new User();
$user->settings = new UserSettings(35, false);

Example::dump($user->settings);
Example::dump($user->getAttributes());
```

The `$user->settings` is:

```plain
LastDragon_ru\LaraASP\Serializer\Docs\Examples\Attribute\UserSettings {
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

[//]: # (end: f35a6e7b23df02996a9581e4c34db9192ceb148e1fa7ed361c57fc6a017bd8e9)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[//]: # (end: e9139abedb89f69284102c9112b548fd7add07cf196259916ea4f1c98977223b)

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: 3f7cfa48046722fb9d277c71e074ff8406787772f90d17405b7554a4464cbfee)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 3f7cfa48046722fb9d277c71e074ff8406787772f90d17405b7554a4464cbfee)

[^1]: <https://laravel.com/docs/eloquent-mutators>
