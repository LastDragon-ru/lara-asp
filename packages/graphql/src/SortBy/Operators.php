<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Operators as BuilderOperators;
use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorChildDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorFieldDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorNullsFirstDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorNullsLastDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;
use Override;

use function array_merge;

class Operators extends BuilderOperators {
    private const Prefix   = Directive::Name.'Operators';
    public const  Extra    = self::Prefix.'Extra';
    public const  Object   = self::Prefix.'Object';
    public const  Disabled = self::Prefix.'Disabled';

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

    public function __construct(
        ContainerResolver $container,
        protected readonly PackageConfig $config,
    ) {
        parent::__construct($container, $this->config->getInstance()->sortBy->operators);
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
