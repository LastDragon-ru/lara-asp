<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

use Exception;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait SaveOrThrow {
    /**
     * @inheritdoc
     */
    public function save(array $options = []) {
        /** @noinspection PhpUndefinedClassInspection */
        return tap(parent::save($options), function (bool $result) {
            if (!$result) {
                throw new Exception('An unknown error occurred while saving the model.');
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function delete() {
        /** @noinspection PhpUndefinedClassInspection */
        return tap(parent::delete(), function (?bool $result) {
            if ($result === false) {
                throw new Exception('An unknown error occurred while deleting the model.');
            }
        });
    }
}
