<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit;

use LastDragon_ru\PhpUnit\Filesystem\Assertions as FilesystemAssertions;
use PHPUnit\Framework\Assert;

/**
 * @mixin Assert
 */
trait Assertions {
    use FilesystemAssertions;
}
