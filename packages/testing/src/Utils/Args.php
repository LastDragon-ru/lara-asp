<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use DOMDocument;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use JsonSerializable;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\Testing\Database\QueryLog\Query;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentDatabaseQuery;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentJson;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentResponse;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentScoutQuery;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentSplFileInfo;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentSplFileInfoIsNotAFile;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentSplFileInfoIsNotReadable;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentXml;
use Psr\Http\Message\ResponseInterface;
use SplFileInfo;
use stdClass;
use Throwable;

use function file_get_contents;
use function is_array;
use function is_scalar;
use function is_string;
use function json_decode;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * @internal
 */
class Args {
    private function __construct() {
        // empty
    }

    public static function content(SplFileInfo|string $file): string {
        if (is_string($file)) {
            return $file;
        }

        $content = file_get_contents(static::getFile($file)->getPathname());

        if ($content === false) {
            throw new InvalidArgumentSplFileInfoIsNotReadable('$file', $file);
        }

        return $content;
    }

    /**
     * @param JsonSerializable|SplFileInfo|stdClass|array<mixed>|string|int|float|bool|null $json
     *
     * @return stdClass|array<mixed>|string|int|float|bool|null
     */
    public static function getJson(
        JsonSerializable|SplFileInfo|stdClass|array|string|int|float|bool|null $json,
        bool $associative = false,
    ): stdClass|array|string|int|float|bool|null {
        if ($json instanceof SplFileInfo) {
            $json = static::content($json);
        }

        if (is_array($json) || $json instanceof JsonSerializable) {
            $json = json_encode($json);
        }

        if (is_string($json)) {
            try {
                $json = json_decode($json, $associative, flags: JSON_THROW_ON_ERROR);
            } catch (Throwable $exception) {
                throw new InvalidArgumentJson('$json', $json, $exception);
            }
        } elseif (is_scalar($json)) {
            // no action
        } else {
            throw new InvalidArgumentJson('$json', $json);
        }

        return $json;
    }

    public static function getJsonString(mixed $json): string {
        $json = json_encode($json);

        if ($json === false) {
            throw new InvalidArgumentJson('$json', $json);
        }

        return $json;
    }

    public static function getJsonPrettyString(mixed $json): string {
        $json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new InvalidArgumentJson('$json', $json);
        }

        return $json;
    }

    public static function getFile(mixed $file): SplFileInfo {
        if (!($file instanceof SplFileInfo)) {
            throw new InvalidArgumentSplFileInfo('$file', $file);
        }

        if (!$file->isFile()) {
            throw new InvalidArgumentSplFileInfoIsNotAFile('$file', $file);
        }

        if (!$file->isReadable()) {
            throw new InvalidArgumentSplFileInfoIsNotReadable('$file', $file);
        }

        return $file;
    }

    public static function getDomDocument(mixed $xml): DOMDocument {
        $dom = null;

        if ($xml instanceof DOMDocument) {
            $dom = $xml;
        } elseif (is_string($xml)) {
            $dom = new DOMDocument();

            if (!$dom->loadXML($xml)) {
                $dom = null;
            }
        } else {
            // empty
        }

        if (!($dom instanceof DOMDocument)) {
            throw new InvalidArgumentXml('$xml', $xml);
        }

        return $dom;
    }

    public static function getResponse(mixed $response): ResponseInterface {
        $psr = null;

        if ($response instanceof ResponseInterface) {
            $psr = $response;
        } else {
            throw new InvalidArgumentResponse('$response', $response);
        }

        return $psr;
    }

    public static function getDatabaseQuery(mixed $query): Query {
        $sql      = null;
        $bindings = [];

        if ($query instanceof QueryBuilder || $query instanceof EloquentBuilder) {
            $sql      = $query->toSql();
            $bindings = $query->getBindings();
        } elseif ($query instanceof Query) {
            $sql      = $query->getQuery();
            $bindings = $query->getBindings();
        } elseif (is_array($query)) {
            $sql      = $query['query'] ?? null;
            $bindings = $query['bindings'] ?? null;
        } elseif (is_string($query)) {
            $sql      = $query;
            $bindings = [];
        } else {
            // empty
        }

        if (!is_string($sql)) {
            throw new InvalidArgumentDatabaseQuery('$query', $query);
        }

        return new Query($sql, $bindings);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getScoutQuery(mixed $query): array {
        $actual  = [];
        $default = [
            'model'         => [],
            'query'         => '',
            'callback'      => null,
            'queryCallback' => null,
            'index'         => null,
            'wheres'        => [],
            'whereIns'      => [],
            'limit'         => null,
            'orders'        => [],
            'options'       => [],
        ];

        if ($query instanceof ScoutBuilder) {
            $actual = json_decode(json_encode($query, JSON_THROW_ON_ERROR), true, JSON_THROW_ON_ERROR);
        } elseif (is_array($query)) {
            $actual = $query;
        } else {
            throw new InvalidArgumentScoutQuery('$query', $query);
        }

        return $actual + $default;
    }
}
