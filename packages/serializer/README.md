# (Laravel) Symfony Serializer

This package provides a customizable wrapper around the [Symfony Serializer Component](https://symfony.com/doc/current/components/serializer.html) to use it inside Laravel application.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: preprocess/78cfc4c7c7c55577)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.4` |   `HEAD ⋯ 8.0.0`   |
|  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `7.2.0 ⋯ 5.0.0-beta.0`   |
|  | `^8.1` |   `6.4.2 ⋯ 5.0.0-beta.0`   |
|  Laravel  | `^12.0.1` |   `HEAD ⋯ 9.0.0`   |
|  | `^11.0.8` |   `8.1.1 ⋯ 8.0.0`   |
|  | `^11.0.0` |   `7.2.0 ⋯ 6.2.0`   |
|  | `^10.34.0` |   `7.2.0 ⋯ 6.2.0`   |
|  | `^10.0.0` |   `6.1.0 ⋯ 5.0.0-beta.0`   |
|  | `^9.21.0` |   `5.6.0 ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |  `5.0.0-beta.0`   |

[//]: # (end: preprocess/78cfc4c7c7c55577)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "serializer"}})
[//]: # (start: preprocess/2a89e018b34da3ed)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-serializer
```

[//]: # (end: preprocess/2a89e018b34da3ed)

# Usage

[include:example]: ./docs/Examples/Usage.php
[//]: # (start: preprocess/4c2bcd97f5d25b12)
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

[//]: # (end: preprocess/4c2bcd97f5d25b12)

# Partial deserialization

Sometimes you don't know (or do not want to support) the full structure of the object. In this case you can mark the class as [`Partial`][code-links/52d699d2b40ed389] to allow unserialize only known (wanted) properties:

[include:example]: ./docs/Examples/Partial.php
[//]: # (start: preprocess/ab12332a02e66fe9)
[//]: # (warning: Generated automatically. Do not edit.)

```php
<?php declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration

namespace LastDragon_ru\LaraASP\Serializer\Docs\Examples\Partial;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\LaraASP\Serializer\Contracts\Partial;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;

class User implements Serializable, Partial {
    public function __construct(
        public string $name,
    ) {
        // empty
    }
}

$serializer   = app()->make(Serializer::class);
$deserialized = $serializer->deserialize(User::class, '{"id":1,"name":"User"}');

Example::dump($deserialized);
```

The `$deserialized` is:

```plain
LastDragon_ru\LaraASP\Serializer\Docs\Examples\Partial\User {
  +name: "User"
}
```

[//]: # (end: preprocess/ab12332a02e66fe9)

# Extending

Out of the box, the package supports only the following objects (see [`Factory`](./src/Factory.php) for more details):

* Any object that implement [`Serializable`](./src/Contracts/Serializable.php) (see [`SerializableNormalizer`](./src/Normalizers/SerializableNormalizer.php))
* Any object that implement `\DateTimeInterface` (see [`DateTimeNormalizer`](./src/Normalizers/DateTimeNormalizer.php))
* `\DateTimeZone`
* `\DateInterval`
* PHP Enums

Publish the config and add normalizers/denormalizers if you need more:

```shell
php artisan vendor:publish --provider=LastDragon_ru\\LaraASP\\Serializer\\PackageProvider --tag=config
```

# Eloquent Accessor/Mutator[^1]

You can use the [`Serialized`][code-links/65c47cc2803dd25a] attribute to populate a model attribute to/from an object:

[include:example]: ./docs/Examples/Attribute.php
[//]: # (start: preprocess/3d9ea93a5e4289d6)
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

[//]: # (end: preprocess/3d9ea93a5e4289d6)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: preprocess/c4ba75080f5a48b7)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: preprocess/c4ba75080f5a48b7)

[^1]: <https://laravel.com/docs/eloquent-mutators>

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/65c47cc2803dd25a]: src/Casts/Serialized.php
    "\LastDragon_ru\LaraASP\Serializer\Casts\Serialized"

[code-links/52d699d2b40ed389]: src/Contracts/Partial.php
    "\LastDragon_ru\LaraASP\Serializer\Contracts\Partial"

[//]: # (end: code-links)
