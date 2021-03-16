<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Utils;

use DOMDocument;
use InvalidArgumentException;
use JetBrains\PhpStorm\NoReturn;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use SplFileInfo;
use stdClass;

use function file_get_contents;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function json_last_error;
use function sprintf;

use const JSON_ERROR_NONE;

/**
 * @internal
 */
class Args {
    private function __construct() {
        // empty
    }

    public static function getFileContents(SplFileInfo $file): string {
        return file_get_contents(static::getFile($file)->getPathname());
    }

    public static function getJson(
        JsonSerializable|SplFileInfo|stdClass|array|string|int|float|bool|null $json,
        bool $associative = false,
    ): stdClass|array|string|int|float|bool|null {
        if ($json instanceof SplFileInfo) {
            $json = static::getFileContents($json);
        }

        if (is_array($json) || $json instanceof JsonSerializable) {
            $json = json_encode($json);
        }

        if (is_string($json)) {
            $json = json_decode($json, $associative);

            if (json_last_error() !== JSON_ERROR_NONE) {
                static::invalidJson();
            }
        } else {
            static::invalidJson();
        }

        return $json;
    }

    public static function getFile(mixed $file): ?SplFileInfo {
        if ($file instanceof SplFileInfo) {
            if (!$file->isReadable()) {
                static::invalidFile();
            }

            return $file;
        }

        return null;
    }

    public static function getDomDocument(mixed $xml): ?DOMDocument {
        $dom = null;

        if ($xml instanceof DOMDocument) {
            $dom = $xml;
        } elseif (is_string($xml)) {
            $dom = new DOMDocument();

            if (!$dom->loadXML($xml)) {
                static::invalidXml();
            }
        } else {
            // empty
        }

        return $dom;
    }

    public static function getResponse(mixed $response): ?ResponseInterface {
        $psr = null;

        if ($response instanceof ResponseInterface) {
            $psr = $response;
        }

        return $psr;
    }

    #[NoReturn]
    public static function invalid(string $message): void {
        throw new InvalidArgumentException($message);
    }

    #[NoReturn]
    public static function invalidFile(): void {
        static::invalid('It is not the file or the file is not readable.');
    }

    #[NoReturn]
    public static function invalidJson(): void {
        static::invalid('It is not a valid JSON.');
    }

    #[NoReturn]
    public static function invalidXml(): void {
        static::invalid('It is not a valid XML.');
    }

    #[NoReturn]
    public static function invalidResponse(): void {
        static::invalid(sprintf('It is not a `%s` instance.', ResponseInterface::class));
    }
}
