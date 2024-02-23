<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as BuilderOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Operators as BuilderOperators;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorChildDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorFieldDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorNullsFirstDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorNullsLastDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Override;

use function array_merge;
use function config;

class Operators extends BuilderOperators {
    public const Extra    = Directive::Name.'Extra';
    public const Object   = Directive::Name.'Object';
    public const Disabled = Directive::Name.'Disabled';

    /**
     * @inheritDoc
     */
    protected array $default = [
        self::Extra  => [
            SortByOperatorFieldDirective::class,
            SortByOperatorNullsFirstDirective::class,
            SortByOperatorNullsLastDirective::class,
        ],
        self::Object => [
            SortByOperatorChildDirective::class,
        ],
    ];

    public function __construct() {
        /** @var array<string, list<class-string<BuilderOperator>|string>> $operators */
        $operators = (array) config(Package::Name.'.sort_by.operators');

        parent::__construct($operators);
    }

    #[Override]
    public function getScope(): string {
        return Scope::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getDisabledOperators(AstManipulator $manipulator): array {
        return array_merge(
            parent::getDisabledOperators($manipulator),
            $this->getTypeOperators($manipulator, self::Disabled),
        );
    }
}
