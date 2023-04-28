<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks;

use Attribute;
use Composer\ClassMapGenerator\ClassMapGenerator;
use GraphQL\Type\Definition\QueryPlan;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnresolvedFieldDefinition;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\TestSettings;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use ReflectionAttribute;
use ReflectionClass;

use function array_unique;
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
        // Actual
        $invalidDefinitions = [];
        $actualDefinitions  = [];
        $actualMap          = ClassMapGenerator::createMap(__DIR__);

        foreach ($actualMap as $name => $path) {
            $class      = new ReflectionClass($name);
            $attributes = $class->getAttributes(GraphQLDefinition::class, ReflectionAttribute::IS_INSTANCEOF);

            if ($attributes) {
                foreach ($attributes as $attribute) {
                    $definition = $attribute->newInstance()->getClass();
                    $instance   = new ReflectionClass($definition);

                    if ($instance->isInstantiable()) {
                        $actualDefinitions[] = $definition;
                    } else {
                        $invalidDefinitions[] = $definition;
                    }
                }
            }
        }

        self::assertEquals([], $invalidDefinitions);
        self::assertNotEmpty($actualDefinitions);

        // Graphql
        $expectedDefinitions = [
            Schema::class,
        ];
        $expectedMap         = new ClassMapGenerator();
        $ignored             = [
            QueryPlan::class                 => true,
            ResolveInfo::class               => true,
            UnresolvedFieldDefinition::class => true,
        ];
        $targets             = [
            Type::class,
        ];

        foreach ($targets as $target) {
            $path = (new ReflectionClass($target))->getFileName();

            if ($path !== false) {
                $expectedMap->scanPaths(dirname($path));
            }
        }

        foreach ($expectedMap->getClassMap()->getMap() as $name => $path) {
            if (isset($ignored[$name])) {
                continue;
            }

            $class = new ReflectionClass($name);

            if ((bool) $class->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF)) {
                continue;
            }

            if ($class->isInstantiable()) {
                $expectedDefinitions[] = $name;
            }
        }

        // Test
        $actualDefinitions   = array_unique($actualDefinitions);
        $expectedDefinitions = array_unique($expectedDefinitions);

        sort($actualDefinitions);
        sort($expectedDefinitions);

        self::assertEquals(
            $expectedDefinitions,
            $actualDefinitions,
        );
    }

    public function testGetContent(): void {
        $context = new Context(new TestSettings(), null, null);
        $content = 'content';
        $block   = Mockery::mock(BlockTest__Block::class, [$context]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->once()
            ->andReturn($content);

        self::assertEquals($content, $block->getContent());
        self::assertEquals($content, $block->getContent());
    }

    public function testGetLength(): void {
        $context = new Context(new TestSettings(), null, null);
        $content = 'content';
        $length  = mb_strlen($content);
        $block   = Mockery::mock(BlockTest__Block::class, [$context]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->once()
            ->andReturn($content);

        self::assertEquals($length, $block->getLength());
        self::assertEquals($length, $block->getLength());
    }

    /**
     * @dataProvider dataProviderIsMultiline
     */
    public function testIsMultiline(bool $expected, Settings $settings, string $content): void {
        $context = new Context($settings, null, null);
        $block   = Mockery::mock(BlockTest__Block::class, [$context]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->once()
            ->andReturn($content);

        self::assertEquals($expected, $block->isMultiline());
        self::assertEquals($expected, $block->isMultiline());
    }

    /**
     * @dataProvider dataProviderIsEmpty
     */
    public function testIsEmpty(bool $expected, string $content): void {
        $context = new Context(new TestSettings(), null, null);
        $block   = Mockery::mock(BlockTest__Block::class, [$context]);
        $block->shouldAllowMockingProtectedMethods();
        $block->makePartial();
        $block
            ->shouldReceive('content')
            ->once()
            ->andReturn($content);

        self::assertEquals($expected, $block->isEmpty());
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
    public function getContent(): string {
        return parent::getContent();
    }

    public function getLength(): int {
        return parent::getLength();
    }

    public function isMultiline(): bool {
        return parent::isMultiline();
    }

    protected function content(): string {
        return '';
    }
}
