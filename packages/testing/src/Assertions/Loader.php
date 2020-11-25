<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

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
        Assert::assertFileIsReadable($file->getPathname());

        return file_get_contents($file->getPathname());
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
}
