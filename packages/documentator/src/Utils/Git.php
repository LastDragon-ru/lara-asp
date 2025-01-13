<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use Illuminate\Process\Factory;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;

use function array_filter;
use function array_values;
use function explode;
use function mb_trim;

readonly class Git {
    public function __construct(
        protected Factory $factory,
    ) {
        // empty
    }

    /**
     * @param callable(string): bool|null $filter
     *
     * @return list<string>
     */
    public function getTags(?callable $filter = null, ?DirectoryPath $root = null): array {
        $tags = $this->run(['git', 'tag', '--list'], $root);
        $tags = explode("\n", $tags);
        $tags = $filter !== null ? array_filter($tags, $filter) : $tags;
        $tags = array_values($tags);

        return $tags;
    }

    public function getFile(string $path, string $revision = 'HEAD', ?DirectoryPath $root = null): string {
        return $this->run(['git', 'show', "{$revision}:{$path}"], $root);
    }

    public function getBranch(?DirectoryPath $root = null): string {
        return $this->run(['git', 'rev-parse', '--abbrev-ref=HEAD'], $root);
    }

    public function getRoot(?DirectoryPath $root = null): DirectoryPath {
        return new DirectoryPath($this->run(['git', 'rev-parse', '--show-toplevel'], $root));
    }

    /**
     * @param array<array-key, string>|string $command
     */
    private function run(array|string $command, ?DirectoryPath $root = null): string {
        $process = $this->factory->newPendingProcess();
        $process = $root !== null ? $process->path((string) $root) : $process->command('');
        $output  = $process->run($command)->throw()->output();
        $output  = mb_trim($output);

        return $output;
    }
}
