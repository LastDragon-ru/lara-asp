<?php declare(strict_types = 1);

// @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

trait NoDocBlockA {
    // empty
}

/**
 * Summary B.
 *
 * Description description description description description description
 * description description description description description description
 * description description description description description description
 */
class NoDocBlockB {
    use NoDocBlockA;
}
