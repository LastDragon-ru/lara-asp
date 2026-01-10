<?php declare(strict_types = 1);

use LastDragon_ru\GraphQLPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\GraphQLPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\Dev\App\Example;
use Nuwave\Lighthouse\Schema\SchemaBuilder;

$schema   = app()->make(SchemaBuilder::class)->schema();
$printer  = app()->make(Printer::class);
$settings = new DefaultSettings();

$printer->setSettings(
    $settings->setDirectiveDefinitionFilter(
        new class() implements DirectiveFilter {
            #[Override]
            public function isAllowedDirective(string $directive, bool $isStandard): bool {
                return !in_array($directive, ['eq', 'all', 'find'], true);
            }
        },
    ),
);

Example::raw($printer->print($schema), 'graphql');
