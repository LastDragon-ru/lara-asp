<?php declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration

use Illuminate\Container\Container;
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

$user         = new User(1, 'User', Date::now());
$serializer   = Container::getInstance()->make(Serializer::class);
$serialized   = $serializer->serialize($user);
$deserialized = $serializer->deserialize(User::class, $serialized);

Example::dump($serialized);
Example::dump($deserialized);
