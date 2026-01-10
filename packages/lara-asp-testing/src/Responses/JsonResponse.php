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

use function array_filter;

class JsonResponse extends Response {
    /**
     * @param JsonSerializable|SplFileInfo|stdClass|array<array-key, mixed>|string|int|float|bool|null $content
     */
    public function __construct(
        StatusCode $code,
        JsonSchema $schema,
        JsonSerializable|SplFileInfo|stdClass|array|string|int|float|bool|null $content = null,
    ) {
        if ($content !== null) {
            $content = Args::getJson($content);
            $content = new JsonMatches(Args::getJsonString($content));
        }

        parent::__construct(
            $code,
            new JsonContentType(),
            new JsonBody(...array_filter([
                new JsonMatchesSchema($schema),
                $content,
            ])),
        );
    }
}
