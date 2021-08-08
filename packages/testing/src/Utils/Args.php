<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use DOMDocument;
use JsonSerializable;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentJson;
use LastDragon_ru\LaraASP\Testing\Exceptions\InvalidArgumentResponse;
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

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
class Args {
    private function __construct() {
        // empty
    }

    public static function content(SplFileInfo|string $file): string {
        return $file instanceof SplFileInfo
            ? file_get_contents(static::getFile($file)->getPathname())
            : $file;
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
}
