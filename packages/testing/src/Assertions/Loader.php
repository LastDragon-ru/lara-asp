<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use DOMDocument;
use InvalidArgumentException;
use JetBrains\PhpStorm\NoReturn;
use PHPUnit\Framework\Assert;
use SplFileInfo;
use stdClass;
use function file_get_contents;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;

/**
 * @internal
 */
class Loader {
    private function __construct() { }

    /**
     * @param \SplFileInfo $file
     *
     * @return string
     */
    public static function loadFile(SplFileInfo $file): string {
        return file_get_contents(static::getFile($file)->getPathname());
    }

    /**
     * @param \SplFileInfo|\stdClass|array|string $json
     *
     * @return \stdClass
     */
    public static function loadJson($json): stdClass {
        if ($json instanceof SplFileInfo) {
            $json = static::loadFile($json);
        }

        if (is_string($json)) {
            Assert::assertJson($json);
        }

        if (is_array($json)) {
            $json = json_encode($json);
        }

        if (is_string($json)) {
            $json = json_decode($json, false);
        }

        return $json;
    }

    /**
     * @param \SplFileInfo|mixed $file
     *
     * @return \SplFileInfo|null
     */
    public static function getFile($file): ?SplFileInfo {
        if ($file instanceof SplFileInfo) {
            Assert::assertFileIsReadable($file->getPathname());

            return $file;
        }

        return null;
    }

    public static function getDomDocument($xml): ?DOMDocument {
        $dom = null;

        if ($xml instanceof DOMDocument) {
            $dom = $xml;
        } elseif (is_string($xml)) {
            $dom = new DOMDocument();

            if (!$dom->loadXML($xml)) {
                static::error('The string is not an XML document.');
            }
        } else {
            // empty
        }

        return $dom;
    }

    #[NoReturn]
    public static function error(string $message): void {
        throw new InvalidArgumentException($message);
    }
}
