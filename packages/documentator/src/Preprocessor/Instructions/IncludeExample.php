<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use Exception;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\PreprocessFailed;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Utils\Process;

use function implode;
use function is_file;
use function pathinfo;
use function preg_match_all;
use function sprintf;
use function trim;

use const PATHINFO_EXTENSION;

class IncludeExample extends IncludeFile {
    public const Limit = 20;

    public function __construct(
        protected readonly Process $process,
    ) {
        parent::__construct();
    }

    public static function getName(): string {
        return 'include:example';
    }

    public function process(string $path, string $target): string {
        $language = $this->getLanguage($path, $target);
        $content  = trim(parent::process($path, $target));
        $content  = <<<CODE
            ```{$language}
            $content
            ```
            CODE;
        $command  = $this->getCommand($path, $target);

        if ($command) {
            try {
                $output = $this->process->run($command, $path);
            } catch (Exception $exception) {
                throw new PreprocessFailed(
                    sprintf(
                        'Failed to execute command `%s`.',
                        implode(' ', $command),
                    ),
                    $exception,
                );
            }

            if (preg_match_all('/\R/u', $output) > static::Limit) {
                $output = <<<CODE
                <details><summary>Output</summary>

                ```plain
                $output
                ```

                </details>
                CODE;
            } else {
                $output = <<<CODE
                Output:

                ```plain
                $output
                ```
                CODE;
            }

            $content .= "\n\n{$output}";
        }

        return $content;
    }

    protected function getLanguage(string $path, string $target): string {
        return pathinfo($target, PATHINFO_EXTENSION);
    }

    /**
     * @return list<string>
     */
    protected function getCommand(string $path, string $target): ?array {
        $info    = pathinfo($target);
        $file    = isset($info['dirname'])
            ? "{$info['dirname']}/{$info['filename']}.run"
            : "{$info['filename']}.run";
        $path    = Path::getPath($path, $file);
        $command = is_file($path) ? [$file] : null;

        return $command;
    }
}
