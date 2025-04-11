<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Markdown;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesUnused;
use Override;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_values;
use function preg_replace_callback;

use const PREG_UNMATCHED_AS_NULL;

/**
 * Includes the `<target>` as a template.
 *
 * @implements InstructionContract<Parameters>
 */
readonly class Instruction implements InstructionContract {
    public function __construct(
        protected Markdown $markdown,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:template';
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
        // Data?
        if ($parameters->data === []) {
            throw new TemplateDataMissed($context, $parameters);
        }

        // Replace
        $vars    = array_keys($parameters->data);
        $used    = [];
        $known   = [];
        $count   = 0;
        $target  = $context->file->getFilePath($parameters->target);
        $target  = ($context->resolver)(new FileReference($target));
        $content = $target->as(Content::class)->content;

        do {
            $content = (string) preg_replace_callback(
                pattern : '/\$\{(?P<name>[^}\s]+)\}/imx',
                callback: static function (array $matches) use ($parameters, &$known, &$used): string {
                    $name         = $matches['name'];
                    $value        = '';
                    $known[$name] = $name;

                    if (array_key_exists($name, $parameters->data)) {
                        $used[$name] = $name;
                        $value       = $parameters->data[$name];
                    }

                    return (string) $value;
                },
                subject : $content,
                count   : $count,
                flags   : PREG_UNMATCHED_AS_NULL,
            );
        } while ($count > 0);

        // Unused?
        $unused = array_diff($vars, $used);

        if ($unused !== []) {
            throw new TemplateVariablesUnused($context, $parameters, array_values($unused));
        }

        // Missed?
        $missed = array_diff($known, $used);

        if ($missed !== []) {
            throw new TemplateVariablesMissed($context, $parameters, array_values($missed));
        }

        // Markdown?
        if ($target->getExtension() === 'md') {
            $content = $this->markdown->parse($content, $target->getPath());
        }

        // Return
        return $content;
    }
}
