<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Uri;
use stdClass;

class JsonSchemaValue implements JsonSchema {
    /**
     * @param JsonSerializable|stdClass|array<mixed>|string $schema
     */
    public function __construct(
        protected JsonSerializable|stdClass|array|string $schema,
    ) {
        // empty
    }

    public function getSchema(): Uri|Schema|stdClass|string {
        return Args::getJson($this->schema);
    }
}
