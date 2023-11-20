<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Concerns;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use Override;

use function is_string;

/**
 * Serializes dates that implements {@see JsonSerializable} by
 * {@see JsonSerializable::jsonSerialize()} instead of hardcoded
 * {@see Carbon::toJSON()}.
 *
 * @see https://carbon.nesbot.com/docs/#api-json
 * @see Carbon
 *
 * @mixin Model
 */
trait WithDateSerialization {
    #[Override]
    protected function serializeDate(DateTimeInterface $date): string {
        $serialized = null;

        if ($date instanceof JsonSerializable) {
            $serialized = $date->jsonSerialize();
        }

        if (!is_string($serialized)) {
            $serialized = parent::serializeDate($date);
        }

        return $serialized;
    }
}
