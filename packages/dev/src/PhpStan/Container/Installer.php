<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\PhpStan\Container;

use Larastan\Larastan\ReturnTypes\ContainerArrayAccessDynamicMethodReturnTypeExtension;
use Larastan\Larastan\ReturnTypes\ContainerMakeDynamicReturnTypeExtension;

use function is_file;
use function str_replace;
use function unlink;

use const PHP_EOL;

class Installer {
    public static function install(): void {
        $path    = 'vendor';
        $root    = 'larastan/larastan/src';
        $classes = [
            ContainerMakeDynamicReturnTypeExtension::class,
            ContainerArrayAccessDynamicMethodReturnTypeExtension::class,
        ];

        foreach ($classes as $class) {
            $file = $path.'/'.str_replace('\\', '/', str_replace('Larastan\\Larastan', $root, $class)).'.php';

            echo "  Removing {$file} ... ";

            if (is_file($file)) {
                echo @unlink($file) ? 'ok' : 'failed';
            } else {
                echo 'not found';
            }

            echo PHP_EOL;
        }
    }
}
