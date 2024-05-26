<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExample;

use Exception;
use Illuminate\Process\Factory;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetExecFailed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Resolvers\FileResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Override;

use function dirname;
use function pathinfo;
use function preg_match;
use function preg_match_all;
use function preg_replace_callback;
use function trim;

use const PATHINFO_FILENAME;
use const PREG_UNMATCHED_AS_NULL;

/**
 * Includes contents of the `<target>` file as an example wrapped into
 * ` ```code block``` `. It also searches for `<target>.run` file, execute
 * it if found, and include its result right after the code block.
 *
 * By default, output of `<target>.run` will be included as ` ```plain text``` `
 * block. You can wrap the output into `<markdown>text</markdown>` tags to
 * insert it as is.
 *
 * @implements InstructionContract<File, null>
 */
class Instruction implements InstructionContract {
    public const    Limit          = 50;
    protected const MarkdownRegexp = '/^\<(?P<tag>markdown)\>(?P<markdown>.*?)\<\/(?P=tag)\>$/msu';

    public function __construct(
        protected readonly Factory $factory,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:example';
    }

    #[Override]
    public static function getResolver(): string {
        return FileResolver::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return null;
    }

    #[Override]
    public function __invoke(Context $context, mixed $target, mixed $parameters): string {
        // Content
        $language = $this->getLanguage($context, $target, $parameters);
        $content  = trim($target->getContent());
        $content  = <<<CODE
            ```{$language}
            $content
            ```
            CODE;

        // Command?
        $command = $this->getCommand($context, $target, $parameters);

        if ($command) {
            // Call
            try {
                $dir    = dirname($target->getPath());
                $output = $this->factory->newPendingProcess()->path($dir)->run($command)->throw()->output();
                $output = trim($output);
            } catch (Exception $exception) {
                throw new TargetExecFailed($context, $exception);
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

    protected function getLanguage(Context $context, File $target, mixed $parameters): string {
        return $target->getExtension();
    }

    protected function getCommand(Context $context, File $target, mixed $parameters): ?string {
        $command = pathinfo($target->getName(), PATHINFO_FILENAME).'.run';
        $command = $context->root->getDirectory($target)?->getFile($command)?->getPath();

        return $command;
    }
}
