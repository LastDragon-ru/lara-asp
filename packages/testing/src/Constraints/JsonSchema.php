<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints;

use Opis\JsonSchema\ISchemaLoader;

class JsonSchema {
    /**
     * @var \SplFileInfo|\stdClass|array|string|null
     */
    protected                $schema;
    protected ?ISchemaLoader $loader;

    /**
     * @param \SplFileInfo|\stdClass|array|string|null $schema
     * @param \Opis\JsonSchema\ISchemaLoader|null      $loader
     */
    public function __construct($schema, ISchemaLoader $loader = null) {
        $this->schema = $schema;
        $this->loader = $loader;
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
