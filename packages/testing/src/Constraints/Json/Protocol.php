<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use LastDragon_ru\LaraASP\Testing\Package;
use Opis\Uri\Uri;
use SplFileInfo;

use function file_get_contents;
use function parse_str;

class Protocol {
    public function getScheme(): string {
        return Package::Name;
    }

    public function __invoke(Uri $uri): ?string {
        // File?
        $file = new SplFileInfo($uri->path());

        if (!$file->isFile() || !$file->isReadable()) {
            return null;
        }

        // Replace parameters
        $params = $this->getParameters($uri);
        $schema = file_get_contents($file->getRealPath());
        $schema = (new Template($schema))->build($params);

        // Return
        return $schema;
    }

    /**
     * @return array<string,string>
     */
    protected function getParameters(Uri $uri): array {
        $parameters = [];

        parse_str($uri->query(), $parameters);

        return $parameters;
    }
}
