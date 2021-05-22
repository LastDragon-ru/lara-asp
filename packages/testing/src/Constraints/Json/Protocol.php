<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use LastDragon_ru\LaraASP\Testing\Package;
use Opis\JsonSchema\Uri;
use SplFileInfo;

use function file_get_contents;
use function http_build_query;

class Protocol {
    public const Scheme = Package::Name;

    /**
     * @param array<string,string> $parameters
     */
    public static function getUri(SplFileInfo $file, array $parameters = []): Uri {
        $scheme = static::Scheme;
        $query  = http_build_query($parameters);
        $uri    = Uri::create("{$scheme}://{$file->getPathname()}?{$query}");

        return $uri;
    }

    public function __invoke(Uri $uri): ?string {
        // File?
        $file = new SplFileInfo($uri->path());

        if (!$file->isFile() || !$file->isReadable()) {
            return null;
        }

        // Replace parameters
        $params = Uri::parseQueryString($uri->query());
        $schema = file_get_contents($file->getRealPath());
        $schema = (new Template($schema))->build($params);

        // Return
        return $schema;
    }
}
