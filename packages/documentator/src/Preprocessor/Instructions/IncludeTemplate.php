<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateDataMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateVariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TemplateVariablesUnused;
use LastDragon_ru\LaraASP\Documentator\Utils\Path;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_values;
use function dirname;
use function file_get_contents;
use function preg_replace_callback;
use function rtrim;

use const PREG_UNMATCHED_AS_NULL;

/**
 * @implements ParameterizableInstruction<IncludeTemplateParameters>
 */
class IncludeTemplate implements ParameterizableInstruction {
    public function __construct() {
        // empty
    }

    public static function getName(): string {
        return 'include:template';
    }

    public static function getDescription(): string {
        return 'Includes the `<target>` as a template.';
    }

    public static function getTargetDescription(): ?string {
        return 'File path.';
    }

    public static function getParameters(): string {
        return IncludeTemplateParameters::class;
    }

    /**
     * @inheritDoc
     */
    public static function getParametersDescription(): array {
        return [
            'data' => 'Array of variables (`${name}`) to replace (required).',
        ];
    }

    public function process(string $path, string $target, Serializable $parameters): string {
        // Content
        $file    = Path::getPath(dirname($path), $target);
        $content = file_get_contents($file);

        if ($content === false) {
            throw new TargetIsNotFile($path, $target);
        }

        // Data?
        if (!$parameters->data) {
            throw new TemplateDataMissed($path, $target);
        }

        // Replace
        $vars  = array_keys($parameters->data);
        $used  = [];
        $known = [];
        $count = 0;

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
            throw new TemplateVariablesUnused($path, $target, array_values($unused));
        }

        // Missed
        $missed = array_diff($known, $used);

        if ($missed) {
            throw new TemplateVariablesMissed($path, $target, array_values($missed));
        }

        // Return
        return rtrim($content)."\n";
    }
}
