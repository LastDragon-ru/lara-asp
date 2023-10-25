<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\VariablesMissed;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Exceptions\VariablesUnused;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(IncludeFile::class)]
class IncludeFileTest extends TestCase {
    public function testProcessRelative(): void {
        $file     = self::getTestData()->file('.md');
        $params   = new IncludeFileParameters();
        $instance = $this->app->make(IncludeFile::class);
        $expected = self::getTestData()->content('.md');

        self::assertEquals($expected, $instance->process($file->getPathname(), $file->getFilename(), $params));
    }

    public function testProcessAbsolute(): void {
        $path     = 'invalid/directory';
        $file     = self::getTestData()->path('.md');
        $params   = new IncludeFileParameters();
        $instance = $this->app->make(IncludeFile::class);
        $expected = self::getTestData()->content('.md');

        self::assertEquals($expected, $instance->process($path, $file, $params));
    }

    public function testProcessVariables(): void {
        $file     = self::getTestData()->file('.md');
        $params   = new IncludeFileParameters([
            'a' => 'A',
            'b' => 'variable ${a}',
        ]);
        $instance = $this->app->make(IncludeFile::class);

        self::assertEquals(
            <<<'FILE'
            # File A

            Content of the file A with variable "variable A"

            FILE
            ,
            $instance->process($file->getPathname(), $file->getFilename(), $params),
        );
    }

    public function testProcessVariablesUnused(): void {
        $file     = self::getTestData()->file('.md');
        $params   = new IncludeFileParameters([
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
        ]);
        $instance = $this->app->make(IncludeFile::class);

        self::expectExceptionObject(
            new VariablesUnused(
                $file->getPathname(),
                $file->getFilename(),
                ['c', 'd'],
            ),
        );

        $instance->process($file->getPathname(), $file->getFilename(), $params);
    }

    public function testProcessVariablesMissed(): void {
        $file     = self::getTestData()->file('.md');
        $params   = new IncludeFileParameters([
            'a' => 'A',
        ]);
        $instance = $this->app->make(IncludeFile::class);

        self::expectExceptionObject(
            new VariablesMissed(
                $file->getPathname(),
                $file->getFilename(),
                ['b'],
            ),
        );

        $instance->process($file->getPathname(), $file->getFilename(), $params);
    }
}
