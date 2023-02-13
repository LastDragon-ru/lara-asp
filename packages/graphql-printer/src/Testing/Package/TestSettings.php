<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package;

use Closure;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\TypeFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\ImmutableSettings;

class TestSettings extends ImmutableSettings {
    protected string           $space                             = ' ';
    protected string           $indent                            = '    ';
    protected string           $fileEnd                           = "\n";
    protected string           $lineEnd                           = "\n";
    protected int              $lineLength                        = 80;
    protected bool             $printDirectives                   = true;
    protected bool             $printDirectiveDefinitions         = true;
    protected bool             $printUnusedDefinitions            = false;
    protected bool             $normalizeSchema                   = true;
    protected bool             $normalizeUnions                   = true;
    protected bool             $normalizeEnums                    = true;
    protected bool             $normalizeInterfaces               = true;
    protected bool             $normalizeFields                   = true;
    protected bool             $normalizeArguments                = true;
    protected bool             $normalizeDescription              = true;
    protected bool             $normalizeDirectiveLocations       = true;
    protected bool             $alwaysMultilineUnions             = true;
    protected bool             $alwaysMultilineArguments          = true;
    protected bool             $alwaysMultilineInterfaces         = true;
    protected bool             $alwaysMultilineDirectiveLocations = true;
    protected ?TypeFilter      $typeDefinitionFilter              = null;
    protected ?DirectiveFilter $directiveFilter                   = null;
    protected ?DirectiveFilter $directiveDefinitionFilter         = null;

    /**
     * @param TypeFilter|Closure(string,bool):bool|null $value
     */
    public function setTypeDefinitionFilter(TypeFilter|Closure|null $value): static {
        if ($value instanceof Closure) {
            $value = $this->makeTypeFilter($value);
        }

        return parent::setTypeDefinitionFilter($value);
    }

    /**
     * @param DirectiveFilter|Closure(string,bool):bool|null $value
     */
    public function setDirectiveFilter(DirectiveFilter|Closure|null $value): static {
        if ($value instanceof Closure) {
            $value = $this->makeDirectiveFilter($value);
        }

        return parent::setDirectiveFilter($value);
    }

    /**
     * @param DirectiveFilter|Closure(string,bool):bool|null $value
     */
    public function setDirectiveDefinitionFilter(DirectiveFilter|Closure|null $value): static {
        if ($value instanceof Closure) {
            $value = $this->makeDirectiveFilter($value);
        }

        return parent::setDirectiveDefinitionFilter($value);
    }

    /**
     * @param Closure(string,bool):bool $closure
     */
    protected function makeTypeFilter(Closure $closure): TypeFilter {
        return new class($closure) implements TypeFilter {
            /**
             * @param Closure(string,bool):bool $filter
             */
            public function __construct(
                protected Closure $filter,
            ) {
                // empty
            }

            public function isAllowedType(string $type, bool $isStandard): bool {
                return ($this->filter)($type, $isStandard);
            }
        };
    }

    /**
     * @param Closure(string, bool):bool $closure
     */
    protected function makeDirectiveFilter(Closure $closure): DirectiveFilter {
        return new class($closure) implements DirectiveFilter {
            /**
             * @param Closure(string, bool):bool $filter
             */
            public function __construct(
                protected Closure $filter,
            ) {
                // empty
            }

            public function isAllowedDirective(string $directive, bool $isStandard): bool {
                return ($this->filter)($directive, $isStandard);
            }
        };
    }
}
