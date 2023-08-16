<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentJsonSchema;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use stdClass;

class JsonSchemaValue implements JsonSchema {
    /**
     * @param JsonSerializable|stdClass|array<array-key, mixed>|string $json
     */
    public function __construct(
        protected JsonSerializable|stdClass|array|string $json,
    ) {
        // empty
    }

    public function getSchema(): stdClass {
        $schema = Args::getJson($this->json);

        if (!($schema instanceof stdClass)) {
            throw new InvalidArgumentJsonSchema('$this->json', $this->json);
        }

        return $schema;
    }
}
