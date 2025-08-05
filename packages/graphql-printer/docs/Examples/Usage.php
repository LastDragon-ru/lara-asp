<?php declare(strict_types = 1);

use GraphQL\Utils\BuildSchema;
use LastDragon_ru\GraphQLPrinter\Printer;
use LastDragon_ru\GraphQLPrinter\Settings\DefaultSettings;
use LastDragon_ru\LaraASP\Dev\App\Example;

$schema   = BuildSchema::build(
    <<<'GRAPHQL'
    type Query {
        a: A
    }

    type A @a {
        id: ID!
        b: [B!]
    }

    type B @b {
        id: ID!
    }

    directive @a on OBJECT
    directive @b on OBJECT
    GRAPHQL,
);
$type     = $schema->getType('A');
$settings = new DefaultSettings();
$printer  = new Printer($settings, null, $schema);

assert($type !== null);

Example::raw($printer->print($type), 'graphql');
Example::raw($printer->export($type), 'graphql');
