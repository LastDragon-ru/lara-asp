<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate;

use Generator;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Move;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Dependencies\FileReference;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeTemplate\Exceptions\TemplateVariablesUnused;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Utils;
use Override;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_values;
use function preg_replace_callback;
use function rtrim;

use const PREG_UNMATCHED_AS_NULL;

/**
 * Includes the `<target>` as a template.
 *
 * @implements InstructionContract<Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:template';
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    /**
     * @return Generator<mixed, Dependency<*>, mixed, string>
     */
    #[Override]
    public function __invoke(Context $context, string $target, mixed $parameters): Generator {
        // Data?
        if (!$parameters->data) {
            throw new TemplateDataMissed($context);
        }

        // Replace
        $vars    = array_keys($parameters->data);
        $used    = [];
        $known   = [];
        $count   = 0;
        $file    = Cast::to(File::class, yield new FileReference($target));
        $content = $file->getContent();

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
        } while ($count);

        // Unused?
        $unused = array_diff($vars, $used);

        if ($unused) {
            throw new TemplateVariablesUnused($context, array_values($unused));
        }

        // Missed?
        $missed = array_diff($known, $used);

        if ($missed) {
            throw new TemplateVariablesMissed($context, array_values($missed));
        }

        // Markdown?
        if ($file->getExtension() === 'md') {
            $path    = $context->file->getPath();
            $content = (new Document($content, $file->getPath()))->mutate(new Move($path));
            $content = $content->toInlinable(Utils::getSeed($context, $file));
            $content = (string) $content;
        }

        // Return
        return rtrim($content)."\n";
    }
}
