<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends EloquentFactory<TModel>
 */
abstract class Factory extends EloquentFactory {
    use WithoutModelEvents;
    use FixRecentlyCreated;
}
