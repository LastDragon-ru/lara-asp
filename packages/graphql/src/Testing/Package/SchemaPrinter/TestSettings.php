<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter;

use Closure;
use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings\ImmutableSettings;

class TestSettings extends ImmutableSettings {
    protected string           $space                             = ' ';
    protected string           $indent                            = '    ';
    protected string           $fileEnd                           = "\n";
    protected string           $lineEnd                           = "\n";
    protected int              $lineLength                        = 80;
    protected bool             $includeDirectives                 = true;
    protected bool             $includeDirectivesInDescription    = false;
    protected bool             $includeUnusedTypeDefinitions      = false;
    protected bool             $includeUnusedDirectiveDefinitions = false;
    protected bool             $normalizeSchema                   = true;
    protected bool             $normalizeUnions                   = true;
    protected bool             $normalizeEnums                    = true;
    protected bool             $normalizeInterfaces               = true;
    protected bool             $normalizeFields                   = true;
    protected bool             $normalizeArguments                = true;
    protected bool             $normalizeDescription              = true;
    protected bool             $normalizeDirectiveLocations       = true;
    protected ?DirectiveFilter $directiveFilter                   = null;

    /**
     * @param DirectiveFilter|Closure(DirectiveNode):bool|null $value
     */
    public function setDirectiveFilter(DirectiveFilter|Closure|null $value): static {
        if ($value instanceof Closure) {
            $value = new class($value) implements DirectiveFilter {
                public function __construct(
                    protected Closure $filter,
                ) {
                    // empty
                }

                public function isAllowedDirective(DirectiveNode $directive): bool {
                    return ($this->filter)($directive);
                }
            };
        }

        return parent::setDirectiveFilter($value);
    }
}
