<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;

class DefaultSettings extends ImmutableSettings {
    protected string           $space                             = ' ';
    protected string           $indent                            = '  ';
    protected string           $fileEnd                           = "\n";
    protected string           $lineEnd                           = "\n";
    protected int              $lineLength                        = 80;
    protected bool             $includeDirectives                 = false;
    protected bool             $includeDirectivesInDescription    = false;
    protected bool             $includeUnusedTypeDefinitions      = true;
    protected bool             $includeUnusedDirectiveDefinitions = true;
    protected bool             $normalizeSchema                   = true;
    protected bool             $normalizeUnions                   = false;
    protected bool             $normalizeEnums                    = false;
    protected bool             $normalizeInterfaces               = false;
    protected bool             $normalizeFields                   = false;
    protected bool             $normalizeArguments                = false;
    protected bool             $normalizeDescription              = false;
    protected bool             $normalizeDirectiveLocations       = false;
    protected ?DirectiveFilter $directiveFilter                   = null;
}
