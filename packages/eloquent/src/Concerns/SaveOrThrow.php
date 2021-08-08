<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin Model|Pivot
 */
trait SaveOrThrow {
    /**
     * @param array<mixed> $options
     */
    public function save(array $options = []): bool {
        $result = parent::save($options);

        if (!$result) {
            throw new Exception('An unknown error occurred while saving the model.');
        }

        return $result;
    }

    public function delete(): bool|int|null {
        /** @var bool|int|null $result */
        $result = parent::delete();

        if ($result === false) {
            throw new Exception('An unknown error occurred while deleting the model.');
        }

        return $result;
    }
}
