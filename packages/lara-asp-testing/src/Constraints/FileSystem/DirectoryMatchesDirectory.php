<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Constraints\FileSystem;

use LastDragon_ru\PhpUnit\Filesystem\Constraints\DirectoryEquals;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\ComparisonFailure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function array_keys;
use function implode;
use function is_array;
use function is_string;
use function ksort;
use function sprintf;

/**
 * @deprecated %{VERSION} The {@see DirectoryEquals} should be used instead.
 *
 * @see DirectoryEquals
 */
class DirectoryMatchesDirectory extends Constraint {
    public function __construct(
        protected string $expected,
    ) {
        // empty
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

        if (array_keys($expected) !== array_keys($actual)) {
            $this->fail(
                $other,
                '',
                new ComparisonFailure(
                    $expected,
                    $actual,
                    $this->export($expected),
                    $this->export($actual),
                ),
            );
        }

        // Compare files
        foreach ($expected as $path => $info) {
            // File?
            if ($info->isDir()) {
                continue;
            }

            // Equal?
            $expectedContent = $info->getContents();
            $actualContent   = ($actual[$path] ?? null)?->getContents();

            if ($expectedContent !== $actualContent) {
                $this->fail(
                    $other,
                    sprintf(
                        'Content of the %s file is different.',
                        Exporter::export($path),
                    ),
                    new ComparisonFailure(
                        $expectedContent,
                        $actualContent,
                        $this->export($expectedContent),
                        $this->export($actualContent),
                    ),
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
     * @param array<string, SplFileInfo>|string $value
     */
    private function export(array|string|null $value): string {
        if (is_array($value)) {
            $value = implode("\n", array_keys($value));
        }

        if (is_string($value)) {
            $value = "'{$value}'";
        } else {
            $value = 'null';
        }

        return $value;
    }
}
