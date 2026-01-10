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
