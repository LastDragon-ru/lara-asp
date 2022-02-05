<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter;

use Closure;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\ImmutableSettings;
use Nuwave\Lighthouse\Support\Contracts\Directive as LighthouseDirective;

class TestSettings extends ImmutableSettings {
    protected string           $space                             = ' ';
    protected string           $indent                            = '    ';
    protected string           $fileEnd                           = "\n";
    protected string           $lineEnd                           = "\n";
    protected int              $lineLength                        = 80;
    protected bool             $printDirectives                   = true;
    protected bool             $printDirectiveDefinitions         = true;
    protected bool             $printDirectivesInDescription      = false;
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
    protected bool             $alwaysMultilineInterfaces         = true;
    protected bool             $alwaysMultilineDirectiveLocations = true;
    protected ?DirectiveFilter $directiveFilter                   = null;

    /**
     * @param DirectiveFilter|Closure(GraphQLDirective|LighthouseDirective):bool|null $value
     */
    public function setDirectiveFilter(DirectiveFilter|Closure|null $value): static {
        if ($value instanceof Closure) {
            $value = new class($value) implements DirectiveFilter {
                public function __construct(
                    protected Closure $filter,
                ) {
                    // empty
                }

                public function isAllowedDirective(GraphQLDirective|LighthouseDirective $directive): bool {
                    return ($this->filter)($directive);
                }
            };
        }

        return parent::setDirectiveFilter($value);
    }
}