<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\MakeInlinable;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document\Move;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Generated\Unwrap;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Contracts\Runner;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Exceptions\ExampleFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Utils;
use Override;
use Throwable;

use function mb_trim;
use function preg_match;
use function preg_match_all;
use function preg_replace_callback;

use const PREG_UNMATCHED_AS_NULL;

/**
 * Includes contents of the `<target>` file as an example wrapped into
 * ` ```code block``` `. If {@see Runner} bound, it will be called to execute
 * the example. Its return value will be added right after the code block.
 *
 * By default, the `Runner` return value will be included as ` ```plain text``` `
 * block. You can wrap the output into `<markdown>text</markdown>` tags to
 * insert it as is.
 *
 * @implements InstructionContract<Parameters>
 */
class Instruction implements InstructionContract {
    public const    int Limit             = 50;
    protected const string MarkdownRegexp = '/^\<(?P<tag>markdown)\>(?P<markdown>.*?)\<\/(?P=tag)\>$/msu';

    public function __construct(
        protected readonly Markdown $markdown,
        protected readonly ?Runner $runner = null,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:example';
    }

    #[Override]
    public static function getPriority(): ?int {
        return null;
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    #[Override]
    public function __invoke(Context $context, InstructionParameters $parameters): Document|string {
        // Content
        $target   = $context->file->getFilePath($parameters->target);
        $target   = ($context->resolver)(new FileReference($target));
        $language = $this->getLanguage($context, $target, $parameters);
        $content  = mb_trim($target->as(Content::class)->content);
        $content  = <<<CODE
            ```{$language}
            $content
            ```
            CODE;

        // Runner?
        if ($this->runner !== null) {
            // Run
            try {
                $output = mb_trim((string) ($this->runner)($target));
            } catch (Throwable $exception) {
                throw new ExampleFailed($context, $parameters, $exception);
            }

            // Markdown?
            $isMarkdown = (bool) preg_match(static::MarkdownRegexp, $output);

            if ($isMarkdown) {
                $output = (string) preg_replace_callback(
                    pattern : static::MarkdownRegexp,
                    callback: static function (array $matches): string {
                        return $matches['markdown'];
                    },
                    subject : $output,
                    flags   : PREG_UNMATCHED_AS_NULL,
                );
                $output = $this->markdown->parse($output, $target->getPath());
                $output = $output
                    ->mutate(
                        new MakeInlinable(Utils::getSeed($context, $output)),
                        new Unwrap(),
                    )
                    ->mutate(
                        new Move($context->file->getPath()),
                    );
                $output = mb_trim((string) $output);
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
            } elseif ($output !== '') {
                $output = <<<CODE
                    Example output:

                    ```plain
                    $output
                    ```
                    CODE;
            } else {
                // empty
            }

            $content .= "\n\n{$output}";
        }

        // Return
        return mb_trim($content);
    }

    protected function getLanguage(Context $context, File $target, Parameters $parameters): ?string {
        return $target->getExtension();
    }
}
