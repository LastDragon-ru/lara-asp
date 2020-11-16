<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

use Exception;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait SaveOrThrow {
    /**
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = []) {
        return tap(parent::save($options), function (bool $result) {
            if (!$result) {
                throw new Exception('An unknown error occurred while saving the model.');
            }
        });
    }

    /**
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete() {
        return tap(parent::delete(), function (?bool $result) {
            if ($result === false) {
                throw new Exception('An unknown error occurred while deleting the model.');
            }
        });
    }
}
