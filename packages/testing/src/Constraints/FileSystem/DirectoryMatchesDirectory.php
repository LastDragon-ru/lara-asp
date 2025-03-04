<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\FileSystem;

use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\Exporter;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function array_keys;
use function implode;
use function is_string;
use function ksort;
use function sprintf;

class DirectoryMatchesDirectory extends Constraint {
    public function __construct(
        protected string $expected,
    ) {
        // empty
    }

    #[Override]
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool {
        try {
            return parent::evaluate($other, $description, $returnResult);
        } catch (ExpectationFailedException $exception) {
            if ($returnResult) {
                return false;
            }

            throw $exception;
        }
    }

    #[Override]
    protected function matches(mixed $other): bool {
        // Directory?
        if (!is_string($other)) {
            return false;
        }

        // Compare files/directories
        $expected = $this->getContent($this->expected);
        $actual   = $this->getContent($other);

        try {
            (new IsIdentical($this->join($expected)))->evaluate($this->join($actual));
        } catch (ExpectationFailedException $exception) {
            $this->fail($other, '', $exception->getComparisonFailure());
        }

        // Compare files
        foreach ($expected as $path => $info) {
            // File?
            if ($info->isDir()) {
                continue;
            }

            // Equal?
            try {
                (new IsIdentical($info->getContents()))->evaluate(($actual[$path] ?? null)?->getContents());
            } catch (ExpectationFailedException $exception) {
                $this->fail(
                    $other,
                    sprintf(
                        'Content of the %s file is different.',
                        Exporter::export($path),
                    ),
                    $exception->getComparisonFailure(),
                );
            }
        }

        // Ok
        return true;
    }

    #[Override]
    public function toString(): string {
        return 'matches directory '.Exporter::export($this->expected);
    }

    /**
     * @return array<string, SplFileInfo>
     */
    protected function getContent(string $directory): array {
        $content  = [];
        $iterator = $this->getFinder($directory);

        foreach ($iterator as $info) {
            $path           = $info->getRelativePathname().($info->isDir() ? '/' : '');
            $content[$path] = $info;
        }

        ksort($content);

        return $content;
    }

    protected function getFinder(string $path): Finder {
        return Finder::create()->ignoreVCS(false)->in($path);
    }

    /**
     * @param array<string, SplFileInfo> $list
     */
    private function join(array $list): string {
        $list = array_keys($list);
        $list = implode("\n", $list);

        return $list;
    }
}
