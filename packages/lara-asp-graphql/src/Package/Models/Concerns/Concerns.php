<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package\Models\Concerns;

use function array_merge;

/**
 * @internal
 */
trait Concerns {
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(string $table, ?string $id = null, array $attributes = []) {
        parent::__construct();

        $this->table        = $table;
        $this->keyType      = 'string';
        $this->incrementing = false;

        $this->forceFill(
            array_merge($attributes, [
                $this->getKeyName() => $id,
            ]),
        );
    }
}
