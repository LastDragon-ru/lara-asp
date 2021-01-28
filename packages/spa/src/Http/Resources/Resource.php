<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class Resource extends JsonResource {
    public static $wrap = null;
}
