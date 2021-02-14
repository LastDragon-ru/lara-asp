<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

use Exception;

use function tap;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait SaveOrThrow {
    /**
     * @inheritdoc
     */
    public function save(array $options = []) {
        /** @noinspection PhpUndefinedClassInspection */
        return tap(parent::save($options), static function (bool $result): void {
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
        return tap(parent::delete(), static function (?bool $result): void {
            if ($result === false) {
                throw new Exception('An unknown error occurred while deleting the model.');
            }
        });
    }
}
