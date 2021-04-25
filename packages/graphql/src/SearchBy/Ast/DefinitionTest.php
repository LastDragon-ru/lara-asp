<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use GraphQL\Language\AST\TypeDefinitionNode;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast\Definition
 */
class DefinitionTest extends TestCase {
    /**
     * @covers ::get
     */
    public function testGet(): void {
        $def        = new class() implements TypeDefinition {
            protected TypeDefinitionNode $node;

            public function __construct() {
                $this->node = new class() implements TypeDefinitionNode {
                    // empty
                };
            }

            public function get(string $name, string $scalar = null, bool $nullable = null): ?TypeDefinitionNode {
                return $this->node;
            }
        };
        $definition = new Definition($this->app, $def::class);

        $a = $definition->get('a');
        $b = $definition->get('b');

        $this->assertNotNull($a);
        $this->assertSame($a, $b);
    }
}
