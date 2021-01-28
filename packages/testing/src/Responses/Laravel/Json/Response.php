<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses\Laravel\Json;

use Illuminate\Http\Resources\Json\JsonResource;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use LastDragon_ru\LaraASP\Testing\Responses\JsonResponse;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use function get_class;

class Response extends JsonResponse {
    use WithTestData;

    public function __construct(StatusCode $code, JsonResource $resource) {
        parent::__construct($code, $this->getTestData(get_class($resource))->file('.json'));
    }
}
