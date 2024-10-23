<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use Nuwave\Lighthouse\Schema\Directives\RenameDirective;

class Config extends Configuration {
    public function __construct(
        /**
         * The list of the directives which should be copied from the original
         * field into the generated `input` field.
         *
         * Important notes:
         * - All other directives except {@see Operator} (for the current
         *   directive) will be ignored.
         * - There are no any checks that directive can be used on
         *   `INPUT_FIELD_DEFINITION`.
         * - The `instanceof` operator is used to check.
         * - Applies for Implicit types only.
         *
         * @see Operator
         *
         * @var list<class-string>
         */
        public array $allowedDirectives = [
            RenameDirective::class,
        ],
    ) {
        parent::__construct();
    }
}
