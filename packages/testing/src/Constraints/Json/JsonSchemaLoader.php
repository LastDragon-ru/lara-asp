<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use LastDragon_ru\LaraASP\Testing\Package;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Opis\JsonSchema\ISchema;
use Opis\JsonSchema\ISchemaLoader;
use Opis\JsonSchema\Schema;
use SplFileInfo;

use function str_starts_with;
use function strlen;
use function substr;

class JsonSchemaLoader implements ISchemaLoader {
    /**
     * Opis\JsonSchema cannot correctly work with Windows-style full paths, so
     * we use a hack and convert them into URLs.
     */
    protected const FullPathPrefix = 'https://example.com/'.Package::Name.'/';

    /**
     * This value used internally in Opis\JsonSchema as prefix for uri.
     */
    protected const RelativePathPrefix = 'json-schema-id:/';

    protected ?string $pwd;

    public function __construct(string $pwd = null) {
        $this->pwd = $pwd;
    }

    public function loadSchema(string $uri): ISchema|null {
        // Supported path?
        $file     = null;
        $relative = static::RelativePathPrefix;

        if ($this->pwd && (str_starts_with($uri, "{$relative}./") || str_starts_with($uri, "{$relative}../"))) {
            $file = new SplFileInfo($this->pwd.'/'.substr($uri, strlen($relative)));
        } elseif (str_starts_with($uri, static::FullPathPrefix)) {
            $file = new SplFileInfo(substr($uri, strlen(static::FullPathPrefix)));
        } else {
            // no action
        }

        // Create
        $schema = null;

        if ($file) {
            $schema = new Schema(Args::getJson($file) ?? Args::invalidJson());
        }

        // Return
        return $schema;
    }

    public static function getLocalSchemaPath(string $path): string {
        return static::FullPathPrefix.$path;
    }
}
