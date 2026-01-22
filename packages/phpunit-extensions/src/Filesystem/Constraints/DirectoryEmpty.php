<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Filesystem\Constraints;

use FilesystemIterator;
use LastDragon_ru\Path\DirectoryPath;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;

class DirectoryEmpty extends Constraint {
    public function __construct() {
        // empty
    }

    #[Override]
    public function toString(): string {
        return 'empty';
    }

    #[Override]
    protected function failureDescription(mixed $other): string {
        return match (true) {
            $other instanceof DirectoryPath => 'directory '.Exporter::export($other->path).' '.$this->toString(),
            default                         => parent::failureDescription($other),
        };
    }

    #[Override]
    protected function matches(mixed $other): bool {
        // Directory?
        if (!($other instanceof DirectoryPath)) {
            return false;
        }

        // Empty?
        $empty    = true;
        $iterator = new FilesystemIterator($other->path);

        foreach ($iterator as $info) {
            $empty = false;
            break;
        }

        return $empty;
    }
}
