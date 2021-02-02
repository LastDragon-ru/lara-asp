<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use Opis\JsonSchema\ISchemaLoader;

class JsonSchema {
    /**
     * @var \JsonSerializable|\SplFileInfo|\stdClass|array|string|null
     */
    protected                $schema;
    protected ?ISchemaLoader $loader;

    /**
     * @param \JsonSerializable|\SplFileInfo|\stdClass|array|string|null $schema
     * @param \Opis\JsonSchema\ISchemaLoader|null                        $loader
     */
    public function __construct($schema, ISchemaLoader $loader = null) {
        $this->schema = $schema;
        $this->loader = $loader ?: new JsonSchemaLoader();
    }

    /**
     * @return array|\SplFileInfo|\stdClass|string|null
     */
    public function getSchema() {
        return $this->schema;
    }

    public function getLoader(): ?ISchemaLoader {
        return $this->loader;
    }
}
