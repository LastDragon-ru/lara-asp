<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts;

use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;

/**
 * @template TParameters of Parameters
 */
interface Instruction {
    public static function getName(): string;

    /**
     * The recommended priority is `null` unless you really need to change it.
     */
    public static function getPriority(): ?int;

    /**
     * @return class-string<Parameters>
     */
    public static function getParameters(): string;

    /**
     * Process target with parameters and return result.
     *
     * The `string` will be placed as is. The `{@see Document}` will be converted
     * to inlinable form to make sure that all related links are valid, and
     * references/footnotes are not conflicting.
     *
     * @param TParameters $parameters
     */
    public function __invoke(Context $context, Parameters $parameters): Document|string;
}
