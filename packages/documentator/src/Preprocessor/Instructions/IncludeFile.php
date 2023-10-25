<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\TargetIsNotFile;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\VariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\VariablesUnused;
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
 * @implements ParameterizableInstruction<IncludeFileParameters>
 */
class IncludeFile implements ParameterizableInstruction {
    public function __construct() {
        // empty
    }

    public static function getName(): string {
        return 'include:file';
    }

    public static function getDescription(): string {
        return 'Includes the `<target>` file.';
    }

    public static function getTargetDescription(): ?string {
        return 'File path.';
    }

    public static function getParameters(): string {
        return IncludeFileParameters::class;
    }

    /**
     * @inheritDoc
     */
    public static function getParametersDescription(): array {
        return [
            'variables' => 'Array of variables (`${name}`) to replace.',
        ];
    }

    public function process(string $path, string $target, Serializable $parameters): string {
        // Content
        $file    = Path::getPath(dirname($path), $target);
        $content = file_get_contents($file);

        if ($content === false) {
            throw new TargetIsNotFile($path, $target);
        }

        // Template?
        if ($parameters->variables) {
            $vars  = array_keys($parameters->variables);
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

                        if (array_key_exists($name, $parameters->variables)) {
                            $used[$name] = $name;
                            $value       = $parameters->variables[$name];
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
                throw new VariablesUnused($path, $target, array_values($unused));
            }

            // Missed
            $missed = array_diff($known, $used);

            if ($missed) {
                throw new VariablesMissed($path, $target, array_values($missed));
            }
        }

        // Return
        return rtrim($content)."\n";
    }
}
