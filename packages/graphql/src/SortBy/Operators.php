<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use Illuminate\Contracts\Config\Repository;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as BuilderOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Operators as BuilderOperators;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;

class Operators extends BuilderOperators {
    public const Extra = 'Extra';

    /**
     * @inheritDoc
     */
    protected array $operators = [
        // empty
    ];

    public function __construct(Repository $config) {
        parent::__construct();

        /** @var array<string,array<class-string<BuilderOperator>|string>> $operators */
        $operators = (array) $config->get(Package::Name.'.sort_by.operators');

        foreach ($operators as $type => $typeOperators) {
            $this->setOperators($type, $typeOperators);
        }
    }

    public function getScope(): string {
        return Directive::getScope();
    }
}
