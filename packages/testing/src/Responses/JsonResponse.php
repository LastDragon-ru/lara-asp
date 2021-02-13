<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Responses;

use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonMatchesSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Json\JsonSchema;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Bodies\JsonBody;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\ContentTypes\JsonContentType;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\Response;
use LastDragon_ru\LaraASP\Testing\Constraints\Response\StatusCode;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use PHPUnit\Framework\Constraint\JsonMatches;
use SplFileInfo;
use stdClass;

use function json_encode;

class JsonResponse extends Response {
    public function __construct(
        StatusCode $code,
        JsonSchema $schema,
        JsonSerializable|SplFileInfo|stdClass|array|string|null $content = null,
    ) {
        if ($content) {
            $content = Args::getJson($content) ?? Args::invalidJson();
        }

        parent::__construct(
            $code,
            new JsonContentType(),
            new JsonBody(...array_filter([
                new JsonMatchesSchema($schema),
                $content
                    ? new JsonMatches(json_encode($content))
                    : null,
            ])),
        );
    }
}
