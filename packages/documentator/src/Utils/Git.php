<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use function array_filter;
use function array_values;
use function explode;

class Git {
    public function __construct(
        protected readonly Process $process,
    ) {
        // empty
    }

    /**
     * @param callable(string): bool|null $filter
     *
     * @return list<string>
     */
    public function getTags(callable $filter = null, string $root = null): array {
        $tags = $this->process->run(['git', 'tag', '--list'], $root);
        $tags = explode("\n", $tags);
        $tags = $filter ? array_filter($tags, $filter) : $tags;
        $tags = array_values($tags);

        return $tags;
    }

    public function getFile(string $path, string $revision = 'HEAD', string $root = null): string {
        return $this->process->run(['git', 'show', "{$revision}:{$path}"], $root);
    }

    public function getBranch(string $root = null): string {
        return $this->process->run(['git', 'rev-parse', '--abbrev-ref=HEAD'], $root);
    }
}
