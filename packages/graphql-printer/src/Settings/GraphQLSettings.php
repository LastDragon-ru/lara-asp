<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Settings;

use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\TypeFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Filters\GraphQLDirectiveFilter;

/**
 * These settings close as possible to {@see \GraphQL\Utils\SchemaPrinter}.
 */
class GraphQLSettings extends ImmutableSettings {
    protected string           $space                             = ' ';
    protected string           $indent                            = '  ';
    protected string           $fileEnd                           = "\n";
    protected string           $lineEnd                           = "\n";
    protected int              $lineLength                        = 80;
    protected bool             $printDirectives                   = true;
    protected bool             $printDirectiveDefinitions         = false;
    protected bool             $printUnusedDefinitions            = true;
    protected bool             $normalizeSchema                   = true;
    protected bool             $normalizeUnions                   = false;
    protected bool             $normalizeEnums                    = false;
    protected bool             $normalizeInterfaces               = false;
    protected bool             $normalizeFields                   = false;
    protected bool             $normalizeArguments                = false;
    protected bool             $normalizeDescription              = false;
    protected bool             $normalizeDirectiveLocations       = false;
    protected bool             $alwaysMultilineUnions             = false;
    protected bool             $alwaysMultilineArguments          = false;
    protected bool             $alwaysMultilineInterfaces         = false;
    protected bool             $alwaysMultilineDirectiveLocations = false;
    protected ?TypeFilter      $typeFilter                        = null;
    protected ?TypeFilter      $typeDefinitionFilter              = null;
    protected ?DirectiveFilter $directiveFilter                   = null;
    protected ?DirectiveFilter $directiveDefinitionFilter         = null;

    public function __construct() {
        parent::__construct();

        $this->directiveFilter = new GraphQLDirectiveFilter();
    }
}
