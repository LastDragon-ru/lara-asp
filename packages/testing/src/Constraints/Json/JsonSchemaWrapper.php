<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use Opis\JsonSchema\ISchemaLoader;
use function str_replace;

class JsonSchemaWrapper extends JsonSchema {
    use WithTestData;

    public function __construct(string $schema, ISchemaLoader $loader = null) {
        $loader = $loader ?: new JsonSchemaLoader($this->getTestData($schema)->file('.any')->getPath());
        $schema = $this->getSchemaFor($schema);

        parent::__construct($schema, $loader);
    }

    protected function getSchemaFor(string $schema): string {
        $ref  = $this->getTestData($schema)->path('.json');
        $base = $this->getTestData()->content('.json');
        $base = str_replace('${schema.path}', "file://{$ref}", $base);

        return $base;
    }
}
