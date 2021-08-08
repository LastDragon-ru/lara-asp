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
     * @inheritdoc
     */
    public function save(array $options = []) {
        $result = parent::save($options);

        if (!$result) {
            throw new Exception('An unknown error occurred while saving the model.');
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function delete() {
        /** @var bool|int|false $result */
        $result = parent::delete();

        if ($result === false) {
            throw new Exception('An unknown error occurred while deleting the model.');
        }

        return $result;
    }
}
