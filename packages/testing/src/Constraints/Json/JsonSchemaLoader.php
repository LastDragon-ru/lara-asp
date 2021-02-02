<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Opis\JsonSchema\ISchemaLoader;
use Opis\JsonSchema\Schema;
use SplFileInfo;
use function str_starts_with;
use function strlen;
use function substr;

class JsonSchemaLoader implements ISchemaLoader {
    protected ?string $pwd;

    public function __construct(string $pwd = null) {
        $this->pwd = $pwd;
    }

    public function loadSchema(string $uri) {
        $prefix = 'json-schema-id:/';
        $schema = null;
        $file   = null;

        if (str_starts_with($uri, 'file:///')) {
            $file = new SplFileInfo(substr($uri, strlen('file://')));
        }

        if ($this->pwd && (str_starts_with($uri, "{$prefix}./") || str_starts_with($uri, "{$prefix}../"))) {
            $file = new SplFileInfo($this->pwd.'/'.substr($uri, strlen($prefix)));
        }

        if ($file) {
            $schema = new Schema(Args::getJson($file) ?? Args::invalidJson());
        }

        return $schema;
    }
}
