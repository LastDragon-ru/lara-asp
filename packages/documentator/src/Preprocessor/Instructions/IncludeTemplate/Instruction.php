<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeTemplate;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateVariablesUnused;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Targets\FileContent;
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
 * @implements InstructionContract<string, Parameters>
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
    public static function getResolver(): string {
        return FileContent::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return Parameters::class;
    }

    #[Override]
    public function process(Context $context, mixed $target, mixed $parameters): string {
        // Data?
        if (!$parameters->data) {
            throw new TemplateDataMissed($context);
        }

        // Replace
        $vars    = array_keys($parameters->data);
        $used    = [];
        $known   = [];
        $count   = 0;
        $content = $target;

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

        // Missed
        $missed = array_diff($known, $used);

        if ($missed) {
            throw new TemplateVariablesMissed($context, array_values($missed));
        }

        // Return
        return rtrim($content)."\n";
    }
}
