<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\Json;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Testing\Package;
use LastDragon_ru\LaraASP\Testing\Utils\Args;
use Opis\JsonSchema\Uri;
use SplFileInfo;
use Symfony\Component\HttpFoundation\HeaderUtils;

use function array_map;
use function explode;
use function http_build_query;
use function implode;
use function ltrim;
use function parse_url;
use function preg_match;
use function rawurldecode;
use function rawurlencode;
use function sprintf;
use function str_replace;

use const PHP_OS_FAMILY;
use const PHP_QUERY_RFC3986;

class Protocol {
    public const    Scheme      = Package::Name;
    protected const HostUnix    = 'unix.path';
    protected const HostWindows = 'windows.path';

    /**
     * @param array<string,string> $parameters
     */
    public static function getUri(SplFileInfo $file, array $parameters = []): Uri {
        // What goings here? Well, Uri class despite the name expects the URL
        // and doesn't allow backslashes in the path. Thus we cannot directly
        // pass Windows paths, so we use the pseudo host and convert backslashes
        // into normal slashes.
        $host = self::HostUnix;
        $path = $file->getPathname();

        if (PHP_OS_FAMILY === 'Windows') {
            $host = self::HostWindows;
            $path = str_replace('\\', '/', $path);
        }

        // Build
        $scheme = static::Scheme;
        $query  = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        $path   = implode('/', array_map(rawurlencode(...), explode('/', ltrim($path, '/'))));
        $uri    = new Uri((array) parse_url("{$scheme}://{$host}/{$path}?{$query}"));

        // Return
        return $uri;
    }

    public function __invoke(Uri $uri): ?string {
        // Our?
        if ($uri->scheme() !== static::Scheme) {
            throw new InvalidArgumentException(sprintf(
                'Scheme `%s` is not supported.',
                $uri->scheme(),
            ));
        }

        // Decode path
        $path = (string) $uri->path();
        $path = implode('/', array_map(rawurldecode(...), explode('/', $path)));

        if ($uri->host() === self::HostWindows) {
            // For Windows it can be `/C:/path`, so we need to remove the slash
            if (preg_match('/^\/[a-z]\:/i', $path)) {
                $path = ltrim($path, '/');
            }
        }

        // File?
        $file = new SplFileInfo($path);

        if (!$file->isFile() || !$file->isReadable()) {
            return null;
        }

        // Replace parameters
        //
        // Uri::parseQueryString() cannot be used because of
        // https://github.com/opis/uri/issues/1
        $params = HeaderUtils::parseQuery((string) $uri->query());
        $schema = Args::content($file);
        $schema = (new Template($schema))->build($params);

        // Return
        return $schema;
    }
}
