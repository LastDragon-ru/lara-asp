<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts;

use Generator;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;

/**
 * @template TParameters of Parameters
 */
interface Instruction {
    public static function getName(): string;

    /**
     * @return class-string<Parameters>
     */
    public static function getParameters(): string;

    /**
     * Process target with parameters and return result.
     *
     * The `string` will be placed as is. The `{@see Document}` will be converted
     * to inlinable form to make sure that all related links are valid, and
     * references/footnotes are not conflicting. And, finally, `Generator`
     * should be used to resolve any file which the Instruction depends on.
     *
     * @see Context::toInlinable()
     * @see Context::toSplittable()
     *
     * @param TParameters $parameters
     *
     * @return Generator<mixed, Dependency<*>, mixed, Document|string>|Document|string
     *      fixme(documentator): The correct type is `Generator<mixed, Dependency<V>, V, Document|string>|Document|string`
     *           but it is not yet supported by phpstan (see https://github.com/phpstan/phpstan/issues/4245)
     */
    public function __invoke(Context $context, string $target, mixed $parameters): Generator|Document|string;
}
