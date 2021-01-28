<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use Illuminate\Http\Resources\Json\JsonResource;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCodes\Created;

class CreatedResponse extends Response {
    public function __construct(JsonResource $resource) {
        parent::__construct(new Created(), $resource);
    }
}
