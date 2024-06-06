<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

/**
 * @mixin Model|Pivot
 */
trait SaveOrThrow {
    /**
     * @param array<array-key, mixed> $options
     */
    #[Override]
    public function save(array $options = []): bool {
        $result = parent::save($options);

        if (!$result) {
            throw new Exception('An unknown error occurred while saving the model.');
        }

        return $result;
    }

    /**
     * @phpstan-ignore-next-line method.childReturnType (`Model::delete()`&`Pivot::::delete()` return different types)
     */
    #[Override]
    public function delete(): bool|int|null {
        $result = parent::delete();

        if ($result === false) { // @phpstan-ignore-line method.childReturnType, identical.alwaysFalse
            throw new Exception('An unknown error occurred while deleting the model.');
        }

        return $result;
    }
}
