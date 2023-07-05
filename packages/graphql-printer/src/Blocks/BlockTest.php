<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use Attribute;
use Composer\ClassMapGenerator\ClassMapGenerator;
use GraphQL\Language\AST\DefinitionNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\Location;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\AST\VariableDefinitionNode;
use GraphQL\Type\Definition\QueryPlan;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnresolvedFieldDefinition;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLMarker;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use ReflectionAttribute;
use ReflectionClass;

use function array_fill_keys;
use function array_unique;
use function array_values;
use function dirname;
use function mb_strlen;
use function sort;

/**
 * @internal
 */
#[CoversClass(Block::class)]
class BlockTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[CoversNothing]
    public function testImplementation(): void {
        $actualMap           = ClassMapGenerator::createMap(__DIR__);
        $actualNodes         = $this->getSupportedClasses(GraphQLAstNode::class, $actualMap);
        $expectedNodes       = $this->getExpectedClasses(
            DefinitionNode::class,
            [],
            [
                // Not needed
                Location::class,
                NodeKind::class,
                NodeList::class,
                NameNode::class,

                // todo(graphql-printer): ExecutableDefinition support
                //      https://github.com/LastDragon-ru/lara-asp/issues/72
                VariableDefinitionNode::class,
                SelectionSetNode::class,
                FieldNode::class,
                FragmentDefinitionNode::class,
                FragmentSpreadNode::class,
                InlineFragmentNode::class,
                OperationDefinitionNode::class,
            ],
        );
        $actualDefinitions   = $this->getSupportedClasses(GraphQLDefinition::class, $actualMap);
        $expectedDefinitions = $this->getExpectedClasses(
            Type::class,
            [
                Schema::class,
            ],
            [
                QueryPlan::class,
                ResolveInfo::class,
                UnresolvedFieldDefinition::class,
            ],
        );

        self::assertEquals(
            array_fill_keys($expectedDefinitions, true),
            array_fill_keys($actualDefinitions, true),
        );
        self::assertEquals(
            array_fill_keys($expectedNodes, true),
            array_fill_keys($actualNodes, true),
        );
    }

    public function testGetContent(): void {
        $used    = 123;
        $level   = 1;
        $context = new Context(new TestSettings(), null, null);
        $content = 'content';
        $block   = Mockery::mock(BlockTest__Block::class, [$context]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->with($level, $used)
            ->once()
            ->andReturn($content);
        $block
            ->shouldReceive('content')
            ->never();

        self::assertEquals($content, $block->getContent($level, $used));
        self::assertEquals($content, $block->getContent($level, $used));
    }

    public function testGetLength(): void {
        $used    = 123;
        $level   = 3;
        $context = new Context(new TestSettings(), null, null);
        $content = 'content';
        $length  = mb_strlen($content);
        $block   = Mockery::mock(BlockTest__Block::class, [$context]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->with($level, $used)
            ->once()
            ->andReturn($content);
        $block
            ->shouldReceive('content')
            ->never();

        self::assertEquals($length, $block->getLength($level, $used));
        self::assertEquals($length, $block->getLength($level, $used));
    }

    /**
     * @dataProvider dataProviderIsMultiline
     */
    public function testIsMultiline(bool $expected, Settings $settings, string $content): void {
        $used    = 1;
        $level   = 23;
        $context = new Context($settings, null, null);
        $block   = Mockery::mock(BlockTest__Block::class, [$context]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->with($level, $used)
            ->once()
            ->andReturn($content);
        $block
            ->shouldReceive('content')
            ->never();

        self::assertEquals($expected, $block->isMultiline($level, $used));
        self::assertEquals($expected, $block->isMultiline($level, $used));
    }

    /**
     * @dataProvider dataProviderIsEmpty
     */
    public function testIsEmpty(bool $expected, string $content): void {
        $used    = 123;
        $level   = 23;
        $context = new Context(new TestSettings(), null, null);
        $block   = Mockery::mock(BlockTest__Block::class, [$context]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->once()
            ->andReturn($content);
        $block
            ->shouldReceive('content')
            ->never();

        self::assertEquals($expected, $block->isEmpty($level, $used));
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =========================================================================
    /**
     * @param class-string<GraphQLMarker> $marker
     * @param array<class-string, string> $map
     *
     * @return list<class-string>
     */
    protected function getSupportedClasses(string $marker, array $map): array {
        $invalid = [];
        $valid   = [];

        foreach ($map as $name => $path) {
            $class      = new ReflectionClass($name);
            $attributes = $class->getAttributes($marker, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                $definition = $attribute->newInstance()->getClass();
                $instance   = new ReflectionClass($definition);

                if ($instance->isInstantiable()) {
                    $valid[] = $definition;
                } else {
                    $invalid[] = $definition;
                }
            }
        }

        sort($valid);
        sort($invalid);

        $valid   = array_values(array_unique($valid));
        $invalid = array_values(array_unique($invalid));

        self::assertEquals([], $invalid);
        self::assertNotEmpty($valid);

        return $valid;
    }

    /**
     * @param class-string       $target
     * @param list<class-string> $classes
     * @param list<class-string> $ignored
     *
     * @return list<class-string>
     */
    protected function getExpectedClasses(string $target, array $classes = [], array $ignored = []): array {
        $ignored = array_fill_keys($ignored, true);
        $file    = (new ReflectionClass($target))->getFileName();

        self::assertIsString($file);

        foreach (ClassMapGenerator::createMap(dirname($file)) as $name => $path) {
            if (isset($ignored[$name])) {
                continue;
            }

            $class = new ReflectionClass($name);

            if ((bool) $class->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF)) {
                continue;
            }

            if ($class->isInstantiable()) {
                $classes[] = $name;
            }
        }

        // Test
        sort($classes);

        $classes = array_values(array_unique($classes));

        return $classes;
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{bool, \LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings, string}>
     */
    public static function dataProviderIsMultiline(): array {
        $settings = new TestSettings();

        return [
            'single short line' => [
                false,
                $settings,
                'short line',
            ],
            'single long line'  => [
                false,
                $settings->setLineLength(5),
                'long line',
            ],
            'multi line'        => [
                true,
                $settings,
                "multi\nline",
            ],
        ];
    }

    /**
     * @return array<string, array{bool, string}>
     */
    public static function dataProviderIsEmpty(): array {
        return [
            'empty'     => [true, ''],
            'non empty' => [false, 'content'],
        ];
    }
    // </editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class BlockTest__Block extends Block {
    public function getContent(int $level, int $used): string {
        return parent::getContent($level, $used);
    }

    public function getLength(int $level, int $used): int {
        return parent::getLength($level, $used);
    }

    public function isMultiline(int $level, int $used): bool {
        return parent::isMultiline($level, $used);
    }

    protected function content(int $level, int $used): string {
        return '';
    }
}
