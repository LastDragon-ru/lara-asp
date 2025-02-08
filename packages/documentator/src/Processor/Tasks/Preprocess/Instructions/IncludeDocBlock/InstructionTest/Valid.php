<?php declare(strict_types = 1);

// @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocBlock\Instruction;

/**
 * Summary [A][link][^1] [B](#fragment) [C][fragment].
 *
 * Description description description description description description
 * description description description description description description
 * description description description description description description
 * [description][link][^1] [description](#fragment) [description][fragment].
 *
 * Description with inline tags:
 *
 * - {@see ValidB}, {@link ValidB}, {@see ValidB::b()}
 * - {@see ValidA}, {@link ValidA}, {@see ValidA::a()}
 * - {@see Instruction}
 * - {@see Instruction::getName()}
 *
 * [link]: https://example.com/
 * [fragment]: #fragment
 *
 * [^1]: Footnote
 *
 * @see https://example.com/
 * @see Instruction
 */
interface ValidA {
    // empty
}

/**
 * Summary B.
 *
 * Description description description description description description
 * description description description description description description
 * description description description description description description
 */
class ValidB {
    // empty
}
