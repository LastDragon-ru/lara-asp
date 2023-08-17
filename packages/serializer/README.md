# The Serializer

> This package is the part of Awesome Set of Packages for Laravel.
>
> [Read more](https://github.com/LastDragon-ru/lara-asp).

This package provides a customizable wrapper around the [Symfony Serializer Component](https://symfony.com/doc/current/components/serializer.html).

# Installation

```shell
composer require lastdragon-ru/lara-asp-serializer
```

# Usage

```php
<?php declare(strict_types = 1);

use DateTimeInterface;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;

use function var_dump;

use const PHP_EOL;

class User implements Serializable {
    public function __construct(
        public int $id,
        public string $name,
        public DateTimeInterface $created,
    ) {
        // empty
    }
}

$user         = new User(1, 'User', Date::now());
$serializer   = Container::getInstance()->make(Serializer::class);
$serialized   = $serializer->serialize($user);
$deserialized = $serializer->deserialize(User::class, $serialized);

echo 'Serialized: ';
var_dump($serialized);
echo PHP_EOL;

echo 'Deserialized: ';
var_dump($deserialized);
```

<details><summary>Output</summary>

```
Serialized: string(64) "{"id":1,"name":"User","created":"2023-08-17T07:06:26.415+00:00"}"

Deserialized: object(Example\User)#470 (3) {
  ["id"]=>
  int(1)
  ["name"]=>
  string(4) "User"
  ["created"]=>
  object(Illuminate\Support\Carbon)#468 (19) {
    ["endOfTime":protected]=>
    bool(false)
    ["startOfTime":protected]=>
    bool(false)
    ["constructedObjectId":protected]=>
    string(32) "00000000000001d40000000000000000"
    ["localMonthsOverflow":protected]=>
    NULL
    ["localYearsOverflow":protected]=>
    NULL
    ["localStrictModeEnabled":protected]=>
    NULL
    ["localHumanDiffOptions":protected]=>
    NULL
    ["localToStringFormat":protected]=>
    NULL
    ["localSerializer":protected]=>
    NULL
    ["localMacros":protected]=>
    NULL
    ["localGenericMacros":protected]=>
    NULL
    ["localFormatFunction":protected]=>
    NULL
    ["localTranslator":protected]=>
    NULL
    ["dumpProperties":protected]=>
    array(3) {
      [0]=>
      string(4) "date"
      [1]=>
      string(13) "timezone_type"
      [2]=>
      string(8) "timezone"
    }
    ["dumpLocale":protected]=>
    NULL
    ["dumpDateProperties":protected]=>
    NULL
    ["date"]=>
    string(26) "2023-08-17 07:06:26.415000"
    ["timezone_type"]=>
    int(1)
    ["timezone"]=>
    string(6) "+00:00"
  }
}
```
</details>

# Extending

Out of the box, the package supports only the following objects (see [`Factory`](./src/Factory.php) for more details):

- Any object that implement [`Serializable`](./src/Contracts/Serializable.php) (see [`SerializableNormalizer`](./src/Normalizers/SerializableNormalizer.php))
- Any object that implement `\DateTimeInterface` (see [`DateTimeNormalizer`](./src/Normalizers/DateTimeNormalizer.php))
- `\DateTimeZone`
- `\DateInterval`

Publish the config and add normalizers/denormalizers if you need more:

```shell
php artisan vendor:publish --provider=LastDragon_ru\\LaraASP\\Serializer\\Provider --tag=config
```
