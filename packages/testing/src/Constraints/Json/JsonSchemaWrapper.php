<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use Opis\JsonSchema\ISchemaLoader;

use function array_map;
use function json_encode;
use function ltrim;
use function strtr;

class JsonSchemaWrapper extends JsonSchema {
    use WithTestData;

    private string $nested;

    public function __construct(string $schema, ISchemaLoader $loader = null) {
        $this->nested = $schema;
        $schema       = $this->getSchemaFor();

        parent::__construct($schema, $loader);
    }

    protected function getSchemaFor(): string {
        $replacements = $this->getSchemaReplacements();
        $replacements = array_map(static function (mixed $value): string {
            return json_encode($value);
        }, $replacements);
        $base         = strtr($this->getBaseSchema(), $replacements);

        return $base;
    }

    protected function getBaseSchema(): string {
        return $this->getTestData()->content('.json');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getSchemaReplacements(): array {
        return [
            '"${schema.path}"' => $this->getLocalPath($this->getTestData($this->nested)->path('.json')),
        ];
    }

    protected function getLocalPath(string $path): string {
        return JsonSchemaLoader::FullPathPrefix.'/'.ltrim($path, '/');
    }

    protected function getDefaultLoader(): JsonSchemaLoader {
        return new JsonSchemaLoader($this->getTestData($this->nested)->file('.any')->getPath());
    }
}
