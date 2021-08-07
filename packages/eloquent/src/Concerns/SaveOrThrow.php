<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Model;

use function tap;

/**
 * @mixin Model
 */
trait SaveOrThrow {
    /**
     * @inheritdoc
     */
    public function save(array $options = []) {
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
        return tap(parent::delete(), static function (?bool $result): void {
            if ($result === false) {
                throw new Exception('An unknown error occurred while deleting the model.');
            }
        });
    }
}
