<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample;

use Exception;
use Illuminate\Support\Facades\Process;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ProcessableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetExecFailed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use Override;

use function dirname;
use function file_get_contents;
use function is_file;
use function pathinfo;
use function preg_match;
use function preg_match_all;
use function preg_replace_callback;
use function trim;

use const PATHINFO_EXTENSION;
use const PREG_UNMATCHED_AS_NULL;

class Instruction implements ProcessableInstruction {
    public const    Limit          = 50;
    protected const MarkdownRegexp = '/^\<(?P<tag>markdown)\>(?P<markdown>.*?)\<\/(?P=tag)\>$/msu';

    public function __construct() {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:example';
    }

    #[Override]
    public static function getDescription(): string {
        return <<<'DESC'
        Includes contents of the `<target>` file as an example wrapped into
        ` ```code block``` `. It also searches for `<target>.run` file, execute
        it if found, and include its result right after the code block.

        By default, output of `<target>.run` will be included as ` ```plain text``` `
        block. You can wrap the output into `<markdown>text</markdown>` tags to
        insert it as is.
        DESC;
    }

    #[Override]
    public static function getTargetDescription(): ?string {
        return 'Example file path.';
    }

    #[Override]
    public function process(string $path, string $target): string {
        // Content
        $file    = Path::getPath(dirname($path), $target);
        $content = file_get_contents($file);

        if ($content === false) {
            throw new TargetIsNotFile($path, $target);
        }

        // Process
        $language = $this->getLanguage($path, $target);
        $content  = trim($content);
        $content  = <<<CODE
            ```{$language}
            $content
            ```
            CODE;
        $command  = $this->getCommand($path, $target);

        if ($command) {
            // Call
            try {
                $output = Process::path(dirname($path))->run($command)->throw()->output();
                $output = trim($output);
            } catch (Exception $exception) {
                throw new TargetExecFailed($path, $target, $exception);
            }

            // Markdown?
            $isMarkdown = (bool) preg_match(static::MarkdownRegexp, $output);

            if ($isMarkdown) {
                $output = trim(
                    (string) preg_replace_callback(
                        pattern : static::MarkdownRegexp,
                        callback: static function (array $matches): string {
                            return $matches['markdown'];
                        },
                        subject : $output,
                        flags   : PREG_UNMATCHED_AS_NULL,
                    ),
                );
            }

            // Format
            $isTooLong = preg_match_all('/\R+/u', $output) > static::Limit;

            if ($isMarkdown && $isTooLong) {
                $output = <<<CODE
                    <details><summary>Example output</summary>

                    {$output}

                    </details>
                    CODE;
            } elseif ($isMarkdown) {
                // as is
            } elseif ($isTooLong) {
                $output = <<<CODE
                    <details><summary>Example output</summary>

                    ```plain
                    {$output}
                    ```

                    </details>
                    CODE;
            } else {
                $output = <<<CODE
                    Example output:

                    ```plain
                    $output
                    ```
                    CODE;
            }

            $content .= "\n\n{$output}";
        }

        // Return
        return $content;
    }

    protected function getLanguage(string $path, string $target): string {
        return pathinfo($target, PATHINFO_EXTENSION);
    }

    protected function getCommand(string $path, string $target): ?string {
        $info    = pathinfo($target);
        $file    = isset($info['dirname'])
            ? Path::join($info['dirname'], "{$info['filename']}.run")
            : "{$info['filename']}.run";
        $command = Path::getPath(dirname($path), $file);
        $command = is_file($command) ? $command : null;

        return $command;
    }
}
