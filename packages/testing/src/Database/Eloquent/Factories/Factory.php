<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;

abstract class Factory extends EloquentFactory {
    use WithoutModelEvents;
    use FixRecentlyCreated;
}
